<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\UserQuestion;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class UserQuestionController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'is_read' => ['nullable', 'boolean'],
            'is_answered' => ['nullable', 'boolean'],
        ]);

        $perPage = (int) ($request->input('per_page', 20));
        $query = UserQuestion::query()->orderByDesc('created_at');

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }
        if ($request->filled('is_answered')) {
            $query->where('is_answered', $request->boolean('is_answered'));
        }

        $questions = $query->paginate($perPage);
        $this->auditLogService->record($request, 'user.questions.listed');

        return response()->json($questions);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:120'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s().-]{7,20}$/', 'max:30'],
            'category' => ['nullable', 'string', 'max:120'],
            'question' => ['required', 'string', 'max:2000'],
        ]);

        $question = UserQuestion::query()->create([
            'user_id' => Auth::id(),
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone'),
            'category' => $request->string('category'),
            'question' => $request->string('question'),
        ]);

        $this->auditLogService->record($request, 'user.question.submitted', $question);

        return response()->json($question, 201);
    }

    public function show(Request $request, UserQuestion $userQuestion): JsonResponse
    {
        $userQuestion->update(['is_read' => true]);
        $this->auditLogService->record($request, 'user.question.viewed');

        return response()->json($userQuestion);
    }

    public function update(Request $request, UserQuestion $userQuestion): JsonResponse
    {
        $request->validate([
            'answer' => ['nullable', 'string', 'max:5000'],
            'is_read' => ['nullable', 'boolean'],
            'is_answered' => ['nullable', 'boolean'],
        ]);

        if ($request->filled('answer')) {
            $userQuestion->answer = $request->string('answer');
            $userQuestion->is_answered = true;
        }
        if ($request->filled('is_read')) {
            $userQuestion->is_read = $request->boolean('is_read');
        }
        $userQuestion->save();

        $this->auditLogService->record($request, 'user.question.updated', $userQuestion);

        return response()->json($userQuestion);
    }
}
