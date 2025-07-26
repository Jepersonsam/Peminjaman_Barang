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
use App\Http\Controllers\RoomControllerApi;
use App\Http\Controllers\RoomLoanControllerApi;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PublicBorrowingControllerApi;
use App\Http\Controllers\WeeklyRoomLoanController;

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


Route::get('/autocomplete-email', [PublicBorrowingControllerApi::class, 'autocompleteEmail']);
Route::get('/borrowings/user/{id}', [PublicBorrowingControllerApi::class, 'userBorrowings']);
Route::get('/room-loans/check-availability', [RoomLoanControllerApi::class, 'checkAvailability']);


// Public route untuk peminjaman tanpa login
Route::get('/public/items', [ItemControllerApi::class, 'publicIndex']);
Route::post('/public/borrowings', [PublicBorrowingControllerApi::class, 'publicStore']);
Route::get('/users/by-code/{code}', [UserControllerApi::class, 'getByCode']);
Route::post('/public/return-item', [BorrowingControllerApi::class, 'returnItem']);

Route::get('rooms', [RoomControllerApi::class, 'index']);
Route::post('rooms', [RoomControllerApi::class, 'store']);
Route::get('rooms/{id}', [RoomControllerApi::class, 'show']);
Route::put('rooms/{id}', [RoomControllerApi::class, 'update']);
Route::delete('rooms/{id}', [RoomControllerApi::class, 'destroy']);

Route::get('room-loans', [RoomLoanControllerApi::class, 'index']);
Route::get('room-loans/by-user/{userId}', [RoomLoanControllerApi::class, 'getByUser']);
Route::post('room-loans', [RoomLoanControllerApi::class, 'store']);
Route::get('room-loans/{id}', [RoomLoanControllerApi::class, 'show']);
Route::put('room-loans/{id}', [RoomLoanControllerApi::class, 'update']);
Route::delete('room-loans/{id}', [RoomLoanControllerApi::class, 'destroy']);




Route::get('/locations', [LocationController::class, 'index']);
Route::post('/locations', [LocationController::class, 'store']);
Route::put('/locations/{id}', [LocationController::class, 'update']);
Route::get('/locations/{id}', [LocationController::class, 'show']);
Route::delete('/locations/{id}', [LocationController::class, 'destroy']);

Route::get('/validate-secret', [LocationController::class, 'validateSecret']);

Route::get('/borrowings', [BorrowingControllerApi::class, 'PublicIndex']);

Route::get('/users/by-nfc/{code_nfc}', [UserControllerApi::class, 'getByNFC']);
Route::get('/public/borrowings/by-code/{code}', [PublicBorrowingControllerApi::class, 'userBorrowingsByCode']);
Route::get('/public/borrowings/by-nfc/{code_nfc}', [PublicBorrowingControllerApi::class, 'userBorrowingsByNFC']);


Route::put('/borrowings/{id}/approve', [BorrowingControllerApi::class, 'approve']);
Route::put('/borrowings/{id}/reject', [BorrowingControllerApi::class, 'reject']);

Route::get('weekly-room-loans', [WeeklyRoomLoanController::class, 'index']);
Route::get('/weekly-room-loans/by-room', [WeeklyRoomLoanController::class, 'getByRoom']);
Route::post('weekly-room-loans', [WeeklyRoomLoanController::class, 'store']);
Route::get('weekly-room-loans/{id}', [WeeklyRoomLoanController::class, 'show']);
Route::put('weekly-room-loans/{id}', [WeeklyRoomLoanController::class, 'update']);
Route::delete('weekly-room-loans/{id}', [WeeklyRoomLoanController::class, 'destroy']);


