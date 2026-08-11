<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\User;
use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Word>
 */
class WordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'menu_id' => null,
            'label' => fake()->unique()->word(),
            'speak_text' => null,
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

    public function forMenu(Menu $menu): static
    {
        return $this->state(fn () => [
            'user_id' => $menu->user_id,
            'menu_id' => $menu->id,
        ]);
    }
}
