<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

final class AdminController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $request->input('from', now('UTC')->subMonths(6)->toDateString());
        $to = $request->input('to', now('UTC')->toDateString());

        $stats = [
            'total_users' => User::query()->count(),
            'verified_users' => User::query()->whereNotNull('email_verified_at')->count(),
            'users_with_2fa' => User::query()->whereNotNull('two_factor_confirmed_at')->count(),
            'total_pharmacists' => DB::table('pharmacists')->count(),
            'active_pharmacists' => DB::table('pharmacists')->where('license_status', 'active')->count(),
            'total_documents' => DB::table('documents')->count(),
            'total_audit_logs' => AuditLog::query()->count(),
            'total_candidacies' => DB::table('candidacies')->count(),
            'pending_candidacies' => DB::table('candidacies')->where('status', 'pending')->count(),
            'total_user_questions' => DB::table('user_questions')->count(),
            'unread_questions' => DB::table('user_questions')->where('is_read', false)->count(),
            'period' => [
                'from' => $from,
                'to' => $to,
            ],
        ];

        $this->auditLogService->record($request, 'admin.stats_viewed');

        return response()->json($stats);
    }

    public function auditLogs(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'action' => ['nullable', 'string', 'max:120'],
        ]);

        $query = AuditLog::query()
            ->with('user')
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        $perPage = (int) ($request->input('per_page', 25));

        $logs = $query->paginate($perPage);

        $this->auditLogService->record($request, 'admin.audit_logs_viewed');

        return response()->json($logs);
    }

    public function users(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'q' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:80'],
        ]);

        $query = User::query()
            ->with('roles:id,name')
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $term = '%' . $request->string('q')->toString() . '%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        if ($request->filled('role')) {
            $query->role($request->string('role')->toString());
        }

        $users = $query->paginate((int) $request->integer('per_page', 25));
        $this->auditLogService->record($request, 'admin.users_viewed');

        return response()->json($users);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        // The route only requires the "Administrateur|Super Admin" role, but role
        // assignment itself must stay Super-Admin-only: otherwise an Administrateur
        // could grant themselves (or anyone) the Super Admin role through this same
        // endpoint, which is a straight privilege escalation.
        abort_if(
            $request->filled('roles') && ! $request->user()?->hasRole('Super Admin'),
            403,
            'Seul un Super Admin peut modifier les roles.'
        );

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:160'],
            'email' => ['sometimes', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:120'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'email_verified' => ['nullable', 'boolean'],
            'two_factor_enabled' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        if (array_key_exists('email_verified', $data)) {
            $user->email_verified_at = $data['email_verified'] ? now('UTC') : null;
        }
        if (array_key_exists('two_factor_enabled', $data) && ! $data['two_factor_enabled']) {
            $user->two_factor_secret = null;
            $user->two_factor_recovery_codes = null;
            $user->two_factor_confirmed_at = null;
        }

        $user->save();

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles']);
        }

        $this->auditLogService->record($request, 'admin.user_updated', $user);

        return response()->json($user->load('roles:id,name'));
    }

    public function deleteUser(Request $request, User $user): JsonResponse
    {
        abort_if($request->user()?->id === $user->id, 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        $this->auditLogService->record($request, 'admin.user_deleted', $user, [
            'email' => $user->email,
        ]);
        $user->delete();

        return response()->json(status: 204);
    }
}
