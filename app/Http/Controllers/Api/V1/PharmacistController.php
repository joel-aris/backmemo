<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Pharmacist\StorePharmacistRequest;
use App\Http\Requests\Pharmacist\UpdatePharmacistRequest;
use App\Models\Pharmacist;
use App\Repositories\PharmacistRepository;
use App\Services\AuditLogService;
use App\Services\PharmacistService;
use App\Services\PharmacistVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class PharmacistController extends Controller
{
    public function __construct(
        private readonly PharmacistRepository $pharmacists,
        private readonly PharmacistService $service,
        private readonly AuditLogService $auditLogService,
        private readonly PharmacistVerificationService $pharmacistVerification,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'commune' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:registered_at,province,last_name'],
            'sort_dir' => ['nullable', 'string', 'in:asc,desc,ASC,DESC'],
        ]);

        $pharmacists = $this->pharmacists->search($data);

        // LengthAwarePaginator has no load() of its own: the call was silently
        // forwarded (via __call) to the underlying Collection, which returns the
        // Collection itself and discards the paginator (current_page/last_page/
        // total). That made pagination past the first page impossible for any
        // client. Eager-load on the underlying collection in place instead, and
        // return the paginator so the metadata survives.
        $pharmacists->getCollection()->load(['province', 'city', 'commune', 'documents']);

        return response()->json($pharmacists);
    }

    public function store(StorePharmacistRequest $request): JsonResponse
    {
        $pharmacist = $this->service->create($request->validated());
        $this->auditLogService->record($request, 'pharmacist.created', $pharmacist);

        return response()->json(['data' => $pharmacist->load(['province', 'city', 'commune'])], 201);
    }

    public function show(Pharmacist $pharmacist): JsonResponse
    {
        return response()->json([
            'data' => $pharmacist->load(['province', 'city', 'commune', 'documents']),
            'cryptographic_proof' => $this->pharmacistVerification->verify($pharmacist),
        ]);
    }

    public function update(UpdatePharmacistRequest $request, Pharmacist $pharmacist): JsonResponse
    {
        $updated = $this->service->update($pharmacist, $request->validated());
        $this->auditLogService->record($request, 'pharmacist.updated', $updated);

        return response()->json(['data' => $updated]);
    }

    public function destroy(Request $request, Pharmacist $pharmacist): JsonResponse
    {
        abort_unless($request->user()?->hasAnyRole(['Super Admin', 'Administrateur']), 403);
        $pharmacist->delete();
        $this->auditLogService->record($request, 'pharmacist.deleted', $pharmacist);

        return response()->json(status: 204);
    }
}
