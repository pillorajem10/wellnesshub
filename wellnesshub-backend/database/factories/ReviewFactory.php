<?php

namespace Database\Factories;

use App\Models\Protocol;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tbl_review_protocol_id' => Protocol::factory(),
            'tbl_review_author_id' => User::factory(),
            'tbl_review_rating' => fake()->numberBetween(1, 5),
            'tbl_review_feedback' => fake()->optional(0.7)->paragraph(),
        ];
    }
}
