<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function (Request $request) {
    return auth()->check() && $request->wantsJson() ?
        response()->json(['status' => 'authenticated', 'message' => 'Already authenticated']) :
        redirect()->away('http://localhost:5173/dashboard');
});

Route::get('/session-check', function () {
    if (!session()->has('test')) {
        session(['test' => 'working']);
        return 'Session variable "test" is not set.';
    }
    return session('test');
});
