<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition()
    {
        return [
            'name' => $this->faker->department,
            'company_id' => Company::factory(),
        ];
    }
}
