<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatController;
use App\Http\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });
    Route::apiResource('/tasks',  TaskController::class)->names('tasks');
    Route::patch('/tasks/{task}', TaskStatusController::class)->name('tasks.status');
    Route::get('/task-stats', TaskStatController::class)->name('tasks.stats');
});
Route::get('/force-logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});
