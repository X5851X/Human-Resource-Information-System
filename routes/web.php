<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'Login gagal. Cek email/password.',
    ]);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});

Route::middleware('auth')->group(function () {
    // Upload routes
    Route::get('/upload', [UploadController::class, 'showUploadForm'])->name('upload.page');
    Route::post('/upload', [UploadController::class, 'upload'])->name('upload.file');
    
    // Employee API routes
    Route::prefix('api')->group(function () {
        // Employee resource routes
        Route::apiResource('employees', EmployeeController::class);
        
        // Additional employee routes
        Route::get('bidang-list', [EmployeeController::class, 'getBidangList'])->name('bidang.list');
        Route::get('jabatan-list', [EmployeeController::class, 'getJabatanList'])->name('jabatan.list');
        Route::get('employees/nip/{nip}', [EmployeeController::class, 'findByNip'])->name('employees.find-by-nip');
        
        // Trash routes
        Route::get('employees/trash', [EmployeeController::class, 'trash'])->name('employees.trash');
        Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
        Route::delete('employees/{id}/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete');
    });
    
    // SPA route - catch all other requests and let the frontend router handle them
    Route::get('/dashboard/{any?}', function () {
        return view('dashboard');
    })->where('any', '.*')->name('dashboard');
});

// Public storage access
Route::get('/storage/{path}', function ($path) {
    return response()->file(storage_path('app/public/' . $path));
})->where('path', '.*')->name('storage.local');

