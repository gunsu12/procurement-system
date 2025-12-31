<?php

namespace Tests\Feature;

use App\Models\ProcurementRequest;
use App\Models\ProcurementItem;
use App\Models\User;
use App\Models\Unit;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_procurement_request_generates_code_on_creation()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $unit = Unit::factory()->create();

        $request = ProcurementRequest::create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
            'company_id' => $company->id,
            'status' => 'draft',
            'request_type' => 'goods',
        ]);

        $this->assertNotNull($request->code);
        $this->assertStringStartsWith('PRC/' . date('Ymd') . '/', $request->code);
    }

    public function test_total_amount_calculation()
    {
        $request = ProcurementRequest::factory()->create();

        ProcurementItem::factory()->create([
            'procurement_request_id' => $request->id,
            'quantity' => 2,
            'estimated_price' => 5000,
        ]);

        ProcurementItem::factory()->create([
            'procurement_request_id' => $request->id,
            'quantity' => 3,
            'estimated_price' => 10000,
        ]);

        // (2 * 5000) + (3 * 10000) = 10000 + 30000 = 40000
        $this->assertEquals(40000, $request->total_amount);
    }

    public function test_hashid_attribute()
    {
        $request = ProcurementRequest::factory()->create();

        $this->assertNotNull($request->hashid);
        $this->assertIsString($request->hashid);
    }
}
