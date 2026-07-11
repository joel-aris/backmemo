<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\AuditLogService;
use App\Models\UserQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ContactController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function message(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'regex:/^[\pL\s\'-]+$/u', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s().-]{7,20}$/', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $question = UserQuestion::query()->create([
            'user_id' => $request->user()?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'category' => $data['subject'],
            'question' => $data['message'],
        ]);

        $this->auditLogService->record($request, 'contact.message_sent', $question, [
            'subject' => $data['subject'],
            'email' => $data['email'],
        ]);

        return response()->json(['message' => 'Message envoye avec succes.'], 201);
    }
}
