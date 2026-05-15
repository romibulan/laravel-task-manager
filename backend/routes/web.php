<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function (Request $request) {
    return auth()->check() && $request->wantsJson() ?
        response()->json(['status' => 'authenticated', 'message' => 'Already authenticated'], 200)
        : redirect()->away(env('FRONTEND_URL', 'http://localhost:3000') . '/login');
});
