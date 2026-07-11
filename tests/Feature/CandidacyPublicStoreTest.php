<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class CandidacyPublicStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_candidacy_without_authentication(): void
    {
        // Regression test: POST /candidacies (public, cahier des charges 4.1)
        // used to be silently shadowed by a duplicate admin-only route
        // registered later in routes/api.php, which made candidacy
        // submission impossible for real visitors.
        $response = $this->postJson('/api/v1/candidacies', [
            'first_name' => 'Jean',
            'last_name' => 'Kabamba',
            'email' => 'jean.kabamba@example.cd',
            'cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('candidacies', ['email' => 'jean.kabamba@example.cd']);
    }
}
