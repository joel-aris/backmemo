<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class OcrExtractTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_call_ocr_extract(): void
    {
        // Regression test: /ocr/extract used to require auth:sanctum, which
        // made it unreachable from its main intended use case, the public
        // (unauthenticated) candidacy form's document scan/pre-fill.
        // Uses a real (tiny, 1x1) JPEG so it survives both the 'image'
        // validation rule and an actual Tesseract pass; the GD extension
        // (needed by UploadedFile::fake()->image()) isn't available here.
        $tinyJpeg = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/'
            . '2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QA'
            . 'FQABAQAAAAAAAAAAAAAAAAAAAAj/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oA'
            . 'DAMBAAIRAxEAPwCdABmX/9k='
        );
        $file = UploadedFile::fake()->createWithContent('card.jpg', $tinyJpeg);

        // Only assert the auth gate is gone, not a 200: whether this
        // resolves to 200 or a tesseract-unavailable 422 depends on
        // whether the tesseract binary is installed in this environment
        // (it is on the deployed server, not necessarily in every dev/CI
        // sandbox), which isn't what this regression test is about.
        $status = $this->postJson('/api/v1/ocr/extract', ['document' => $file])->getStatusCode();
        $this->assertNotContains($status, [401, 403]);
    }

    public function test_guest_must_upload_an_image(): void
    {
        $this->postJson('/api/v1/ocr/extract', [
            'document' => UploadedFile::fake()->create('card.pdf', 100, 'application/pdf'),
        ])->assertUnprocessable();
    }
}
