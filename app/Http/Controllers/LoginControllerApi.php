<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\LoginRequest;
use App\Http\Responses\LoginResponse;


class LoginControllerApi extends Controller
{
    /**
     * login
     *
     * @param  mixed $request
     * @return void
     */
    public function login(LoginRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return LoginResponse::failed();
        }

        return LoginResponse::success($user);
    }
}
