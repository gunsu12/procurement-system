<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'division_id' => Division::factory(),
        ];
    }
}
