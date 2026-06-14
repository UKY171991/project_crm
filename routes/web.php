<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Cron Job Routes
Route::get('/cron/pending-tasks', [\App\Http\Controllers\CronController::class, 'sendPendingTasksEmail']);

Route::get('/cron/run-scheduler', [\App\Http\Controllers\CronController::class, 'runScheduler']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    Route::resource('websites', \App\Http\Controllers\WebsiteController::class);
    
    Route::get('non-clients', function() {
        return view('clients.non-clients');
    })->name('clients.non-clients');

    
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

        Route::get('loans', function() {
            return view('loans.index');
        })->name('loans.index');
        
        Route::resource('clients', \App\Http\Controllers\ClientController::class);


        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::get('settings', function() {
            return view('settings.index');
        })->name('settings.index');

        // Master Only System Tools
        Route::middleware(['role:master'])->group(function () {
            Route::match(['get', 'post'], 'system/composer-update', [\App\Http\Controllers\DashboardController::class, 'runComposerUpdate'])->name('system.composer-update');
        });

        // Master & Admin System Tools
        Route::middleware(['role:master,admin'])->group(function () {
            Route::match(['get', 'post'], 'system/clear-cache', [\App\Http\Controllers\DashboardController::class, 'clearCache'])->name('system.clear-cache');
            Route::match(['get', 'post'], 'system/run-migration', [\App\Http\Controllers\DashboardController::class, 'runMigration'])->name('system.run-migration');
            Route::match(['get', 'post'], 'system/fix-storage', [\App\Http\Controllers\DashboardController::class, 'fixStorageLink'])->name('system.fix-storage');
        });
        
        // Admin & Master System Tools
        Route::middleware(['role:master,admin'])->group(function () {

            
            // WhatsApp Management
            Route::get('whatsapp/settings', function() {
                return view('settings.whatsapp');
            })->name('whatsapp.settings');
            Route::get('whatsapp/test-connection', [\App\Http\Controllers\WhatsAppController::class, 'testConnection'])->name('whatsapp.test-connection');
            Route::post('whatsapp/send-test', [\App\Http\Controllers\WhatsAppController::class, 'sendTestMessage'])->name('whatsapp.send-test');
        });
        
        Route::get('payments', function() {
            return view('payments.index');
        })->name('payments.index');

        // HR & Salary
        Route::get('hr', function() {
            return view('hr.index');
        })->name('hr.index');
        
        Route::get('hr/salary-slip', [\App\Http\Controllers\HRController::class, 'generateSalarySlip'])->name('hr.salary-slip');
        

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

// WhatsApp Webhook Routes (outside auth middleware)
Route::get('/webhook/whatsapp', function() {
    return response()->json([
        'status' => 'active',
        'message' => 'WhatsApp Webhook is active. Use POST requests to deliver payload events.'
    ]);
});
Route::get('/webhook/whatsapp/verify', [\App\Http\Controllers\WhatsAppController::class, 'verifyWebhook']);
Route::post('/webhook/whatsapp', [\App\Http\Controllers\WhatsAppController::class, 'handleWebhook']);
