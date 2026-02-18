<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Cron Job Route
Route::get('/cron/pending-tasks', [\App\Http\Controllers\CronController::class, 'sendPendingTasksEmail']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    
    // Attendance
    Route::get('attendance', function() {
        return view('attendance.index');
    })->name('attendance.index');
    
    // Media Uploads
    Route::post('projects/{project}/upload-image', [\App\Http\Controllers\MediaController::class, 'storeImage'])->name('projects.upload-image');
    Route::post('projects/{project}/upload-video', [\App\Http\Controllers\MediaController::class, 'storeVideo'])->name('projects.upload-video');

    // Assignments
    Route::post('projects/{project}/assign', [\App\Http\Controllers\ProjectAssignmentController::class, 'store'])->name('projects.assign');
    Route::delete('projects/{project}/assign/{user}', [\App\Http\Controllers\ProjectAssignmentController::class, 'destroy'])->name('projects.unassign');
    
    // Admin/Master Only Routes
    Route::middleware(['role:master|admin'])->group(function () {
        // Expenses
        Route::get('expenses', function() {
            return view('expenses.index');
        })->name('expenses.index');
        
        Route::resource('clients', \App\Http\Controllers\ClientController::class);
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::get('settings', function() {
            return view('settings.index');
        })->name('settings.index');

        // Master Only System Tools
        Route::middleware(['role:master'])->group(function () {
            Route::post('system/clear-cache', [\App\Http\Controllers\DashboardController::class, 'clearCache'])->name('system.clear-cache');
            Route::post('system/run-migration', [\App\Http\Controllers\DashboardController::class, 'runMigration'])->name('system.run-migration');
            Route::post('system/composer-update', [\App\Http\Controllers\DashboardController::class, 'runComposerUpdate'])->name('system.composer-update');
            Route::post('system/fix-storage', [\App\Http\Controllers\DashboardController::class, 'fixStorageLink'])->name('system.fix-storage');
        });
        Route::get('payments', function() {
            return view('payments.index');
        })->name('payments.index');

        // HR & Salary
        Route::get('hr', function() {
            return view('hr.index');
        })->name('hr.index');
        
        Route::get('hr/salary-slip', [\App\Http\Controllers\HRController::class, 'generateSalarySlip'])->name('hr.salary-slip');
        
        // Screenshots
        Route::get('screenshots', function() {
            return view('screenshots.index');
        })->name('screenshots.index');
    });



    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('notifications/mark-read', function() {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.mark-read');
});

require __DIR__.'/auth.php';
