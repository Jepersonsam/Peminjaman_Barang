<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class RegisterResponse
{
    public static function success($user, $token): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Register success!',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public static function failed(string $message = 'Register failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 400);
    }
}
