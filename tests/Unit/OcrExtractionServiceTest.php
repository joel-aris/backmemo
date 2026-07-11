<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\OcrExtractionService;
use PHPUnit\Framework\TestCase;

final class OcrExtractionServiceTest extends TestCase
{
    private OcrExtractionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OcrExtractionService();
    }

    public function test_parses_ordinal_number_name_and_expiration_from_professional_card(): void
    {
        $text = <<<'TXT'
            ORDRE DES PHARMACIENS DE LA RDC
            CARTE PROFESSIONNELLE

            Nom: Kabamba
            Prenom: Jean

            N° Ordre: PH-RDC-2026-000123
            Date d'expiration: 15/08/2027
            TXT;

        $fields = $this->service->parseFields($text);

        $this->assertSame('Kabamba', $fields['last_name']);
        $this->assertSame('Jean', $fields['first_name']);
        $this->assertSame('PH-RDC-2026-000123', $fields['ordinal_number']);
        $this->assertSame('2027-08-15', $fields['license_expires_at']);
    }

    public function test_parses_license_number_label_variant(): void
    {
        $text = "Licence N°: LIC-99887\nValable jusqu'au 01/01/2030";

        $fields = $this->service->parseFields($text);

        $this->assertSame('LIC-99887', $fields['license_number']);
        $this->assertSame('2030-01-01', $fields['license_expires_at']);
    }

    public function test_returns_nulls_when_nothing_recognizable_is_found(): void
    {
        $fields = $this->service->parseFields('bruit de scan illisible sans structure');

        $this->assertNull($fields['ordinal_number']);
        $this->assertNull($fields['license_number']);
        $this->assertNull($fields['license_expires_at']);
        $this->assertNull($fields['first_name']);
        $this->assertNull($fields['last_name']);
    }
}
