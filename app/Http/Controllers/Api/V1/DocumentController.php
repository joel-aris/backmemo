<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Models\Document;
use App\Services\AuditLogService;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $service,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $documents = Document::query()
            ->with('pharmacist:id,public_id,first_name,last_name,license_number')
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json($documents);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = $this->service->store($request->validated(), $request->user()->id);
        $this->auditLogService->record($request, 'document.uploaded', $document);

        return response()->json(['data' => $document], 201);
    }

    public function show(Document $document): JsonResponse
    {
        $proof = $this->service->verify($document);

        return response()->json([
            'data' => $document->load('pharmacist', 'owner'),
            'cryptographic_proof' => $proof,
        ]);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:180'],
            'status' => ['sometimes', 'in:uploaded,signed,revoked,archived'],
        ]);
        $document->fill($data)->save();
        $this->auditLogService->record($request, 'document.updated', $document);

        return response()->json(['data' => $document->refresh()]);
    }

    public function destroy(Request $request, Document $document): JsonResponse
    {
        $document->delete();
        $this->auditLogService->record($request, 'document.deleted', $document);

        return response()->json(status: 204);
    }

    public function sign(Request $request, Document $document): JsonResponse
    {
        $signed = $this->service->sign($document);
        $this->auditLogService->record($request, 'document.signed', $signed);

        return response()->json(['data' => $signed]);
    }

    public function verify(Request $request, Document $document): JsonResponse
    {
        $proof = $this->service->verify($document);
        $this->auditLogService->record($request, 'document.verified', $document, $proof);

        return response()->json($proof);
    }
}
