<?php

namespace Database\Factories;

use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    protected $model = Vote::class;

    /**
     * Always pass tbl_vote_votable_id (and tbl_vote_votable_type if not a thread) when creating votes.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tbl_vote_user_id' => User::factory(),
            'tbl_vote_votable_id' => 0,
            'tbl_vote_votable_type' => Thread::class,
            'tbl_vote_value' => fake()->randomElement([1, 1, 1, -1]),
        ];
    }
}
