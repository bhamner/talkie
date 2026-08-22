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
            'is_builtin' => false,
            'is_hidden' => false,
        ];
    }

    public function template(): static
    {
        return $this->state(fn () => [
            'user_id' => null,
            'is_builtin' => true,
        ]);
    }

    public function builtin(): static
    {
        return $this->state(fn () => [
            'is_builtin' => true,
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
