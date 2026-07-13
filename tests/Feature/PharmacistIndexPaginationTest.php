<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Pharmacist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PharmacistIndexPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_pagination_metadata_alongside_the_list(): void
    {
        // Regression test: PharmacistController::index() used to call ->load()
        // directly on the LengthAwarePaginator returned by search(). Paginator
        // has no load() of its own, so the call was forwarded (via __call) to
        // the underlying Collection, which returns the Collection itself and
        // silently discards the paginator (current_page/last_page/total). Any
        // registry with more results than per_page was unreachable past page 1.
        Pharmacist::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/pharmacists?per_page=2')->assertOk();

        $response->assertJsonPath('current_page', 1);
        $response->assertJsonPath('last_page', 2);
        $response->assertJsonPath('total', 3);
        $this->assertCount(2, $response->json('data'));
    }
}
