<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TaskListController;
use App\Http\Controllers\API\TaskController;

// Public routes - NO authentication
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('test', function() {
    return response()->json(['status' => 'ok']);
});

// Protected routes - REQUIRE authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::put('user/profile', [AuthController::class, 'updateProfile']);
    
    // Task List routes - THESE MUST BE INSIDE auth:sanctum
    Route::apiResource('task-lists', TaskListController::class);
    
    // Task routes
    Route::post('task-lists/{taskList}/tasks', [TaskController::class, 'store']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
});