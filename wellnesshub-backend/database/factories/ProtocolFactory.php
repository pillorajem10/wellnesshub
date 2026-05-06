<?php

namespace Database\Factories;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Protocol>
 */
class ProtocolFactory extends Factory
{
    protected $model = Protocol::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(5, true);

        return [
            'tbl_protocol_title' => ucfirst($title),
            'tbl_protocol_slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'tbl_protocol_content' => collect(range(1, 3))
                ->map(fn () => fake()->paragraph())
                ->implode("\n\n"),
            'tbl_protocol_tags' => fake()->randomElements(
                ['sleep', 'recovery', 'stress', 'anxiety', 'hydration', 'mobility', 'productivity', 'breathing', 'wellness', 'circadian-rhythm'],
                fake()->numberBetween(2, 5)
            ),
            'tbl_protocol_author_id' => User::factory(),
        ];
    }
}
