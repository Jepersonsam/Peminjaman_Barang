<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
//use Illuminate\Support\Facades\Validator;
use App\Http\Responses\RegisterResponse;

class RegisterControllerApi extends Controller
{

    /**
     * register
     *
     * @param  mixed $request
     * @return void
     */

    public function register(RegisterRequest $request)
{
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // Jika ada 'roles' dikirim di request dan user adalah superadmin/admin
    if ($request->has('roles')) {
        $user->syncRoles($request->roles); // ['admin', 'user']
    } else {
        // Assign default role 'user'
        $user->assignRole('user');
    }

    return RegisterResponse::success('Register Success', $user->load('roles'));
}

}
