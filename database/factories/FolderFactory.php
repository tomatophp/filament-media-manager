<?php

namespace TomatoPHP\FilamentMediaManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use TomatoPHP\FilamentMediaManager\Models\Folder;

/**
 * @extends Factory<Folder>
 */
class FolderFactory extends Factory
{
    protected $model = Folder::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => $name,
            'collection' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'icon' => 'heroicon-o-folder',
            'color' => $this->faker->hexColor(),
            'is_protected' => false,
            'password' => null,
        ];
    }

    public function protected(string $password = 'secret123'): self
    {
        return $this->state(fn (array $attributes) => [
            'is_protected' => true,
            'password' => $password,
        ]);
    }

    public function withPassword(string $password): self
    {
        return $this->state(fn (array $attributes) => [
            'password' => $password,
        ]);
    }

    public function public(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_protected' => false,
            'password' => null,
        ]);
    }
}
