<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'province_id' => Province::inRandomOrder()->first()?->id ?? Province::factory(),
            'name' => $this->faker->city(),
            'code' => strtoupper($this->faker->regexify('[A-Z]{5}')),
        ];
    }
}