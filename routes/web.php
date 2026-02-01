<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    
    // Media Uploads
    Route::post('projects/{project}/upload-image', [\App\Http\Controllers\MediaController::class, 'storeImage'])->name('projects.upload-image');
    Route::post('projects/{project}/upload-video', [\App\Http\Controllers\MediaController::class, 'storeVideo'])->name('projects.upload-video');

    // Assignments
    Route::post('projects/{project}/assign', [\App\Http\Controllers\ProjectAssignmentController::class, 'store'])->name('projects.assign');
    Route::delete('projects/{project}/assign/{user}', [\App\Http\Controllers\ProjectAssignmentController::class, 'destroy'])->name('projects.unassign');

    // Admin/Master Only Routes
    Route::middleware(['role:master|admin'])->group(function () {
        Route::resource('clients', \App\Http\Controllers\ClientController::class);
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::get('settings', function() {
            return view('settings.index');
        })->name('settings.index');
    });

    Route::get('payments', function() {
        return view('payments.index');
    })->name('payments.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
