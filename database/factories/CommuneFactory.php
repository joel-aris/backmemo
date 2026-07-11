<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Commune;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CommuneFactory extends Factory
{
    protected $model = Commune::class;

    public function definition(): array
    {
        return [
            'province_id' => Province::inRandomOrder()->first()?->id ?? Province::factory(),
            'city_id' => City::inRandomOrder()->first()?->id ?? City::factory(),
            'name' => $this->faker->unique()->citySuffix(),
        ];
    }
}