<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class OcrExtractTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_call_ocr_extract(): void
    {
        $file = UploadedFile::fake()->create('card.jpg', 100, 'image/jpeg');

        $this->postJson('/api/v1/ocr/extract', ['document' => $file])
            ->assertUnauthorized();
    }

    public function test_authenticated_user_must_upload_an_image(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/ocr/extract', [
            'document' => UploadedFile::fake()->create('card.pdf', 100, 'application/pdf'),
        ])->assertUnprocessable();
    }
}
