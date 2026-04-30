<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Auth;

Route::get('/user', function (Request $request) {
    return new UserResource($request->user());
})->middleware('auth:sanctum');

Route::apiResource('/tasks',  TaskController::class)->names('tasks')->middleware('auth:sanctum');
Route::patch('/tasks/{task}', TaskStatusController::class)->name('tasks.status')->middleware('auth:sanctum');
Route::get('/force-logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});
