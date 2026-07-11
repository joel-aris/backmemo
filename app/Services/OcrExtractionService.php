<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Throwable;

final class OcrExtractionService
{
    /**
     * Runs OCR on the given image path and returns the raw text plus a
     * best-effort guess of the pharmacist onboarding fields (name, ordre
     * number, license/expiration date) found in a professional card,
     * diploma or ID document.
     *
     * @return array{raw_text: string, fields: array<string, string|null>}
     */
    public function extract(string $imagePath, string $lang = 'fra+eng'): array
    {
        $text = $this->runTesseract($imagePath, $lang);

        return [
            'raw_text' => $text,
            'fields' => $this->parseFields($text),
        ];
    }

    /**
     * Pure text -> fields parsing, split out from extract() so it can be
     * unit-tested without invoking the tesseract binary.
     *
     * @return array<string, string|null>
     */
    public function parseFields(string $text): array
    {
        return [
            'first_name' => $this->guessFirstName($text),
            'last_name' => $this->guessLastName($text),
            'ordinal_number' => $this->guessOrdinalNumber($text),
            'license_number' => $this->guessLicenseNumber($text),
            'license_expires_at' => $this->guessExpirationDate($text),
        ];
    }

    private function runTesseract(string $imagePath, string $lang): string
    {
        try {
            return (new TesseractOCR($imagePath))
                ->lang(...explode('+', $lang))
                ->run();
        } catch (Throwable $e) {
            Log::warning('OCR extraction failed', ['error' => $e->getMessage()]);

            throw new \RuntimeException(
                "Le moteur OCR (tesseract) n'a pas pu traiter ce document. Verifiez qu'il est installe sur le serveur (apt install tesseract-ocr tesseract-ocr-fra) et reessayez, ou saisissez les informations manuellement.",
                previous: $e,
            );
        }
    }

    private function guessOrdinalNumber(string $text): ?string
    {
        return $this->matchAfterLabel($text, ['n[°o]?\s*(?:d\'?\s*)?ordre', 'numero d\'?ordre', 'ordre n[°o]?', 'ordinal']);
    }

    private function guessLicenseNumber(string $text): ?string
    {
        return $this->matchAfterLabel($text, ['n[°o]?\s*(de\s*)?licence', 'licence n[°o]?', 'license\s*n[°o]?', 'matricule']);
    }

    private function matchAfterLabel(string $text, array $labelPatterns): ?string
    {
        foreach ($labelPatterns as $label) {
            if (preg_match('/(?:'.$label.')\s*[:\-]?\s*([A-Z0-9][A-Z0-9\/._-]{2,30})/iu', $text, $m)) {
                return strtoupper(trim($m[1]));
            }
        }

        return null;
    }

    private function guessExpirationDate(string $text): ?string
    {
        $labelled = null;
        if (preg_match('/(?:expir\w*|valide? jusqu\'?au|date d\'?expiration)\D{0,10}(\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4})/iu', $text, $m)) {
            $labelled = $m[1];
        }

        $candidate = $labelled;
        if ($candidate === null && preg_match_all('/\b(\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4})\b/u', $text, $all) && $all[1] !== []) {
            // No explicit label found: fall back to the last date on the
            // document (ID/diploma dates are often "issued" then "expires").
            $candidate = end($all[1]);
        }

        if ($candidate === null) {
            return null;
        }

        foreach (['d/m/Y', 'd-m-Y', 'd.m.Y', 'd/m/y', 'd-m-y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $candidate)?->format('Y-m-d');
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    private function guessFirstName(string $text): ?string
    {
        return $this->matchNameAfterLabel($text, ['pr[eé]nom', 'first\s*name', 'given\s*name']);
    }

    private function guessLastName(string $text): ?string
    {
        return $this->matchNameAfterLabel($text, ['nom\s*de\s*famille', 'last\s*name', 'surname', 'nom(?!\s*d[e\']\s*naissance)']);
    }

    private function matchNameAfterLabel(string $text, array $labelPatterns): ?string
    {
        foreach ($labelPatterns as $label) {
            // Space/tab only (not \n) after the label, so a name never
            // swallows the next line's label when the field is left blank.
            if (preg_match('/(?:'.$label.')\s*[:\-]?[ \t]*([\p{L}][\p{L} \'-]{1,60})/iu', $text, $m)) {
                $value = trim(preg_replace('/[ \t]+/', ' ', $m[1]));

                return $value === '' ? null : $value;
            }
        }

        return null;
    }
}
