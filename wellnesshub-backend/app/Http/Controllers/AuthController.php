<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'tbl_user_fname' => $data['fname'],
            'tbl_user_lname' => $data['lname'],
            'tbl_user_email' => $data['email'],
            'tbl_user_password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return $this->successResponse([
            'user' => $this->formatUser($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->where('tbl_user_email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->tbl_user_password)) {
            return $this->errorResponse('Invalid credentials.', [], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        return $this->successResponse([
            'user' => $this->formatUser($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($this->formatUser($request->user()));
    }

    /**
     * @return array<string, mixed>
     */
    private function formatUser(User $user): array
    {
        return [
            'tbl_user_id' => $user->tbl_user_id,
            'tbl_user_fname' => $user->tbl_user_fname,
            'tbl_user_lname' => $user->tbl_user_lname,
            'tbl_user_email' => $user->tbl_user_email,
            'tbl_user_created_at' => $user->tbl_user_created_at,
            'tbl_user_updated_at' => $user->tbl_user_updated_at,
        ];
    }
}
