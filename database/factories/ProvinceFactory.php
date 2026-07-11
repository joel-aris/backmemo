<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->region,
            'code' => strtoupper($this->faker->unique()->regexify('[A-Z]{3}')),
        ];
    }
}