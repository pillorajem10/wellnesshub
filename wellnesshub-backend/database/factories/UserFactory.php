<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Seeded and factory users use password: user123
     */
    protected static ?string $password;

    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tbl_user_fname' => fake()->firstName(),
            'tbl_user_lname' => fake()->lastName(),
            'tbl_user_email' => fake()->unique()->safeEmail(),
            'tbl_user_password' => static::$password ??= Hash::make('user123'),
        ];
    }
}
