<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\LoginControllerApi;
use App\Http\Controllers\RegisterControllerApi;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserControllerApi;
use App\Http\Controllers\ItemControllerApi;
use App\Http\Controllers\BorrowingControllerApi;




// Public routes (no authentication required)
Route::post('/register', [RegisterControllerApi::class, 'register']);
Route::post('/login', [LoginControllerApi::class, 'login']);

Route::middleware('auth:api')->get('/me', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
});


// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {

    // Dashboard route - all authenticated users can access
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Dashboard data']);
    })->middleware('can:view-dashboard');

    // User Management Routes
    Route::middleware('can:manage users')->group(function () {
        Route::get('/users', [UserControllerApi::class, 'index'])->middleware('can:view-users');
        Route::get('/users/{id}', [UserControllerApi::class, 'show'])->middleware('can:view-users');
        Route::post('/users', [UserControllerApi::class, 'store'])->middleware('can:create-users');
        Route::put('/users/{id}', [UserControllerApi::class, 'update'])->middleware('can:edit-users');
        Route::delete('/users/{id}', [UserControllerApi::class, 'destroy'])->middleware('can:delete-users');
    });


    // Permission Management Routes
    Route::middleware('can:manage permissions')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->middleware('can:view-permissions');
        Route::post('/permissions', [PermissionController::class, 'store'])->middleware('can:create-permissions');
        Route::get('/permissions/{id}', [PermissionController::class, 'show'])->middleware('can:view-permissions');
        Route::put('/permissions/{id}', [PermissionController::class, 'update'])->middleware('can:edit-permissions');
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->middleware('can:delete-permissions');
    });

    // Role Management Routes
    Route::middleware('can:manage roles')->group(function () {
        Route::get('/roles/only-names', [RoleController::class, 'onlyNames'])->middleware('can:view-roles');
        Route::get('/roles', [RoleController::class, 'index'])->middleware('can:view-roles');
        Route::post('/roles', [RoleController::class, 'store'])->middleware('can:create-roles');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('can:view-roles');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('can:edit-roles');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('can:delete-roles');
    });


    // Item Management Routes
    Route::middleware('can:manage items')->group(function () {
        Route::get('/items', [ItemControllerApi::class, 'index'])->middleware('can:view-items');
        Route::post('/items', [ItemControllerApi::class, 'store'])->middleware('can:create-items');
        Route::get('/items/{id}', [ItemControllerApi::class, 'show'])->middleware('can:view-items');
        Route::put('/items/{id}', [ItemControllerApi::class, 'update'])->middleware('can:edit-items');
        Route::delete('/items/{id}', [ItemControllerApi::class, 'destroy'])->middleware('can:delete-items');
    });

    // Borrowing Management Routes
    Route::middleware('can:manage borrowing')->group(function () {
        Route::get('/borrowings', [BorrowingControllerApi::class, 'index'])->middleware('can:view-borrowing');
        Route::post('/borrowings', [BorrowingControllerApi::class, 'store'])->middleware('can:create-borrowing');
        Route::get('/borrowings/{id}', [BorrowingControllerApi::class, 'show'])->middleware('can:view-borrowing');
        Route::put('/borrowings/{id}', [BorrowingControllerApi::class, 'update'])->middleware('can:edit-borrowing');
        Route::delete('/borrowings/{id}', [BorrowingControllerApi::class, 'destroy'])->middleware('can:delete-borrowing');
    });
});

// Public route untuk peminjaman tanpa login
Route::get('/public/items', [ItemControllerApi::class, 'publicIndex']);
Route::post('/public/borrowings', [BorrowingControllerApi::class, 'publicStore']);
Route::get('/users/by-code/{code}', [UserControllerApi::class, 'getByCode']);
Route::post('/public/return-item', [BorrowingControllerApi::class, 'returnItem']);
