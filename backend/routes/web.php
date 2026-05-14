<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function (Request $request) {
    return auth()->check() ?
        response()->json(['status' => 'authenticated', 'message' => 'Already authenticated'], 200) :
        redirect()->away('https://taskmanager-tzmk.onrender.com/login');
});

Route::get('/session-check', function () {
    if (!session()->has('test')) {
        session(['test' => 'working']);
        return 'Session variable "test" is not set.';
    }
    return session('test');
});
