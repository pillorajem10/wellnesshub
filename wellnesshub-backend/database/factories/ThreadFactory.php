<?php

namespace Database\Factories;

use App\Models\Protocol;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tbl_thread_protocol_id' => Protocol::factory(),
            'tbl_thread_author_id' => User::factory(),
            'tbl_thread_title' => fake()->sentence(rand(6, 10)),
            'tbl_thread_body' => fake()->paragraphs(rand(2, 4), true),
            'tbl_thread_tags' => fake()->optional(0.6)->randomElements(
                ['sleep', 'recovery', 'stress', 'wellness', 'breathing'],
                rand(1, 3)
            ),
        ];
    }
}
