<?php

namespace Database\Factories;

use App\Models\ProcurementItem;
use App\Models\ProcurementRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcurementItemFactory extends Factory
{
    protected $model = ProcurementItem::class;

    public function definition()
    {
        return [
            'procurement_request_id' => ProcurementRequest::factory(),
            'name' => $this->faker->words(3, true),
            'specification' => $this->faker->sentence,
            'quantity' => $this->faker->numberBetween(1, 100),
            'estimated_price' => $this->faker->numberBetween(1000, 1000000),
            'unit' => $this->faker->randomElement(['pcs', 'box', 'set']),
        ];
    }
}
