<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Candidacy;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class CandidacyController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        $perPage = (int) ($request->input('per_page', 20));
        $query = Candidacy::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $candidacies = $query->paginate($perPage);
        $this->auditLogService->record($request, 'candidacies.listed');

        return response()->json($candidacies);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'last_name' => ['required', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:120'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s().-]{7,20}$/', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'motivation_letter' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $cvPath = null;
        $cvMime = null;
        $cvSize = null;

        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('candidacies/cv', 'public');
            $cvMime = $request->file('cv')->getMimeType();
            $cvSize = $request->file('cv')->getSize();
        }

        $motivationPath = null;
        $motivationMime = null;
        $motivationSize = null;

        if ($request->hasFile('motivation_letter')) {
            $motivationPath = $request->file('motivation_letter')->store('candidacies/motivation', 'public');
            $motivationMime = $request->file('motivation_letter')->getMimeType();
            $motivationSize = $request->file('motivation_letter')->getSize();
        }

        $candidacy = Candidacy::query()->create([
            'user_id' => Auth::id(),
            'first_name' => $request->string('first_name'),
            'last_name' => $request->string('last_name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone'),
            'address' => $request->string('address'),
            'notes' => $request->string('notes'),
            'cv_path' => $cvPath,
            'cv_mime_type' => $cvMime,
            'cv_size' => $cvSize,
            'motivation_letter_path' => $motivationPath,
            'motivation_letter_mime_type' => $motivationMime,
            'motivation_letter_size' => $motivationSize,
            'status' => 'pending',
        ]);

        $this->auditLogService->record($request, 'candidacy.submitted', $candidacy);

        return response()->json($candidacy, 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = (int) ($request->input('per_page', 20));

        $candidacies = Candidacy::query()
            ->where('user_id', $request->user()?->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $this->auditLogService->record($request, 'candidacies.mine_listed');

        return response()->json($candidacies);
    }

    public function show(Request $request, Candidacy $candidacy): JsonResponse
    {
        $this->auditLogService->record($request, 'candidacy.viewed');

        return response()->json($candidacy);
    }

    public function update(Request $request, Candidacy $candidacy): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'string', 'max:30'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->filled('status')) {
            $candidacy->status = $request->string('status');
        }
        if ($request->filled('admin_notes')) {
            $candidacy->admin_notes = $request->string('admin_notes');
        }
        $candidacy->save();

        $this->auditLogService->record($request, 'candidacy.updated', $candidacy);

        return response()->json($candidacy);
    }
}
