<?php

namespace TomatoPHP\FilamentMediaManager\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TomatoPHP\FilamentMediaManager\Tests\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
        ];
    }
}
