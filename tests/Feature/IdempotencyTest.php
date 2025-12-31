<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Unit;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_request_prevents_duplicate_submission_with_same_key()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $unit = Unit::factory()->create(['company_id' => $user->company_id]);

        $payload = [
            'idempotency_key' => 'test-key-123',
            'unit_id' => $unit->id,
            'request_type' => 'nonaset',
            'items' => [
                ['name' => 'Item 1', 'quantity' => 1, 'estimated_price' => 100, 'unit' => 'pcs']
            ]
        ];

        // First request
        $response1 = $this->post(route('procurement.store'), $payload);
        $response1->assertStatus(302);
        $response1->assertSessionHas('success');

        // Second request with same key
        $response2 = $this->post(route('procurement.store'), $payload);
        $response2->assertStatus(302);
        $response2->assertSessionHas('warning', 'Request is already being processed. Please wait.');
    }
}
