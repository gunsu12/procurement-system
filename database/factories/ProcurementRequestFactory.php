<?php

namespace Database\Factories;

use App\Models\ProcurementRequest;
use App\Models\User;
use App\Models\Unit;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcurementRequestFactory extends Factory
{
    protected $model = ProcurementRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'unit_id' => Unit::factory(),
            'company_id' => Company::factory(),
            'status' => 'pending',
            'notes' => $this->faker->sentence,
            'request_type' => $this->faker->randomElement(['goods', 'services']),
            'is_medical' => $this->faker->boolean,
            'is_cito' => false,
        ];
    }
}
