<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'parent_id' => null,
            'name' => fake()->unique()->word(),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }

    public function template(): static
    {
        return $this->state(fn () => [
            'user_id' => null,
        ]);
    }

    public function childOf(Menu $parent): static
    {
        return $this->state(fn () => [
            'user_id' => $parent->user_id,
            'parent_id' => $parent->id,
        ]);
    }
}
