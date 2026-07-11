<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Document;
use App\Repositories\PharmacistRepository;
use App\Services\AuditLogService;
use App\Services\CryptographyService;
use App\Services\PharmacistVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class PublicVerificationController extends Controller
{
    public function __construct(
        private readonly PharmacistRepository $pharmacists,
        private readonly AuditLogService $auditLogService,
        private readonly CryptographyService $crypto,
        private readonly PharmacistVerificationService $pharmacistVerification,
    ) {}

    public function __invoke(Request $request, string $qrCode): JsonResponse
    {
        $pharmacist = $this->pharmacists->findPublic($qrCode);

        if ($pharmacist) {
            $proof = $this->pharmacistVerification->verify($pharmacist);
            $this->auditLogService->record($request, 'public.pharmacist_verified', $pharmacist, ['valid' => $proof['valid']]);

            return response()->json([
                'type' => 'pharmacist',
                'valid' => $proof['valid'],
                'data' => $pharmacist->load(['province', 'city', 'commune']),
                'cryptographic_proof' => $proof,
            ]);
        }

        $document = Document::query()->where('qr_code_token', $qrCode)->first();
        if ($document) {
            $valid = $this->crypto->verifySignature(
                (string) $document->signature_payload,
                (string) $document->signature,
                $document->public_key,
            );
            $this->auditLogService->record($request, 'public.document_verified', $document, ['valid' => $valid]);

            return response()->json([
                'type' => 'document',
                'valid' => $valid,
                'data' => $document->load('pharmacist'),
                'cryptographic_proof' => [
                    'signature_algorithm' => $document->signature_algorithm,
                    'public_key_fingerprint' => $document->public_key_fingerprint,
                    'trusted_timestamp' => $document->trusted_timestamp?->toIso8601String(),
                    'verified_at' => now('UTC')->toIso8601String(),
                ],
            ]);
        }

        return response()->json(['type' => 'unknown', 'valid' => false, 'message' => 'Aucune ressource trouvee.'], 404);
    }
}
