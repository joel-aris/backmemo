<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Faq;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class FaqController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'category' => ['nullable', 'string', 'max:120'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $query = Faq::query()->where('is_active', true)->orderBy('order')->orderByDesc('created_at');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->get(['id', 'question', 'answer', 'category']);
        $this->auditLogService->record($request, 'faqs.listed');

        return response()->json($faqs);
    }

    public function categories(Request $request): JsonResponse
    {
        $categories = Faq::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $this->auditLogService->record($request, 'faqs.categories');

        return response()->json($categories);
    }
}
