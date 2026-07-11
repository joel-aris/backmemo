<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\SearchHistory;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class SearchHistoryController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = (int) ($request->input('per_page', 20));
        $query = SearchHistory::query()
            ->when(Auth::check(), fn ($q) => $q->where('user_id', Auth::id()), fn ($q) => $q->whereNull('user_id'))
            ->orderByDesc('created_at');

        $history = $query->paginate($perPage);
        $this->auditLogService->record($request, 'search.history');

        return response()->json($history);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'max:255'],
        ]);

        $data = [
            'user_id' => Auth::id(),
            'query' => $request->string('query'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        $history = SearchHistory::query()->create($data);
        $this->auditLogService->record($request, 'search.stored');

        return response()->json($history, 201);
    }
}
