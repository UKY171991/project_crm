<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



// Get authenticated user info (for desktop app)
Route::middleware('auth:sanctum')->get('/user-info', function (Request $request) {
    return response()->json([
        'id' => auth()->id(),
        'name' => auth()->user()->name,
        'email' => auth()->user()->email
    ]);
});

Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    
    Route::get('/dashboard', [\App\Http\Controllers\Api\ApiController::class, 'dashboard']);
    Route::get('/projects', [\App\Http\Controllers\Api\ApiController::class, 'projects']);
    Route::get('/websites', [\App\Http\Controllers\Api\ApiController::class, 'websites']);
    Route::get('/non-clients', [\App\Http\Controllers\Api\ApiController::class, 'nonClients']);
    Route::get('/non-clients/{id}', [\App\Http\Controllers\Api\ApiController::class, 'viewNonClient']);
    Route::post('/non-clients/{id}/status', [\App\Http\Controllers\Api\ApiController::class, 'updateNonClientStatus']);
    Route::post('/non-clients/{id}/feedback', [\App\Http\Controllers\Api\ApiController::class, 'addNonClientFeedback']);
});
