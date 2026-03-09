<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password has been reset.'])
            : response()->json(['message' => 'Invalid token or email.'], 400);
    }

    public function validateToken(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return response()->json([
                'valid' => false,
                'message' => 'Token atau email tidak ditemukan.',
            ], 400);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return response()->json([
                'valid' => false,
                'message' => 'Token tidak valid atau kadaluarsa.',
            ], 404);
        }

        // ✅ Cek apakah token cocok (ingat, token di-hash di DB)
        if (!Hash::check($token, $record->token)) {
            return response()->json([
                'valid' => false,
                'message' => 'Token tidak cocok atau sudah kadaluarsa.',
            ], 401);
        }

        // ✅ Cek masa berlaku token (default 60 menit)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > config('auth.passwords.users.expire', 60)) {
            return response()->json([
                'valid' => false,
                'message' => 'Token sudah kadaluarsa.',
            ], 410);
        }

        return response()->json([
            'valid' => true,
            'email' => $record->email,
            'message' => 'Token valid.',
        ]);
    }
}
