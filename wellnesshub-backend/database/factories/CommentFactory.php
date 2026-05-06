<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tbl_comment_thread_id' => Thread::factory(),
            'tbl_comment_author_id' => User::factory(),
            'tbl_comment_parent_id' => null,
            'tbl_comment_body' => fake()->paragraph(),
        ];
    }
}
