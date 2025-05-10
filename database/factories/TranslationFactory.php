<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'key_' . Str::uuid(),
            'value' => fake()->sentence(),
            'locale' => fake()->randomElement(['en', 'fr', 'es'])
        ];
    }
}