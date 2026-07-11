<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Ocr\ExtractOcrRequest;
use App\Services\AuditLogService;
use App\Services\OcrExtractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use RuntimeException;

final class OcrController extends Controller
{
    public function __construct(
        private readonly OcrExtractionService $ocrService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    /**
     * Scans a professional card, diploma or ID document (photo/scan) and
     * returns a best-effort pre-fill of the pharmacist onboarding fields
     * (name, ordre number, license/expiration date). The uploaded image is
     * processed in place from the temp upload and never persisted to disk.
     */
    public function extract(ExtractOcrRequest $request): JsonResponse
    {
        $file = $request->file('document');

        try {
            $result = $this->ocrService->extract($file->getRealPath());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $this->auditLogService->record($request, 'ocr.extracted', metadata: [
            'fields_found' => array_keys(array_filter($result['fields'])),
        ]);

        return response()->json($result);
    }
}
