<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

// =======================
// Public routes
// =======================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/ping', function() {
    return response()->json(['status' => 'online'], 200);
});

// =======================
// Protected routes
// =======================
Route::middleware('auth:sanctum')->group(function () {

    // -------- Auth --------
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // -------- Projects --------
    Route::get('/projects', [ProjectController::class, 'index']);      // liste paginée
    Route::post('/projects', [ProjectController::class, 'store']);     // création
    Route::get('/projects/{id}', [ProjectController::class, 'show']);  // détail
    Route::get('/projects/{id}/users', [ProjectController::class, 'getUsers']);  // utilisateurs du projet

    Route::middleware('project.owner')->group(function () {
        Route::put('/projects/{id}', [ProjectController::class, 'update']);     // modification
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']); // suppression
        Route::post('/projects/{id}/tasks', [TaskController::class, 'store']);  // création tâche
    });

    // -------- Tasks --------
    Route::get('/projects/{id}/tasks', [TaskController::class, 'index']); // tâches d'un projet
    Route::get('/tasks/{id}', [TaskController::class, 'show']); // détail d'une tâche

    Route::middleware('task.permission')->group(function () {
        Route::put('/tasks/{id}', [TaskController::class, 'update']);           // modification
        Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']); // changement de statut
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy']); // suppression
    });
});
