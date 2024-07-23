<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;

// Routes accessible without authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::group(['middleware' => ['auth:api', 'role:admin']], function () {
    Route::post('/admin-route', [AdminController::class, 'adminMethod']);
});

Route::group(['middleware' => ['auth:api', 'permission:manage users']], function () {
    Route::post('/manage-users-route', [UserController::class, 'manageUsers']);
});



