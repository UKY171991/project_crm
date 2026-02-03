<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScreenshotController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Screenshot API routes (no auth for desktop app)
Route::post('/screenshot-upload', [ScreenshotController::class, 'upload']);
Route::post('/get-active-attendance', [ScreenshotController::class, 'getActiveAttendance']);
Route::get('/get-active-attendance', [ScreenshotController::class, 'getActiveAttendance']);

Route::match(['post', 'options'], '/login', [ScreenshotController::class, 'login']);
Route::match(['post', 'options'], '/get-work-stats', [ScreenshotController::class, 'getWorkStats']);
Route::match(['post', 'options'], '/clock-out', [ScreenshotController::class, 'clockOut']);

// Get authenticated user info (for desktop app)
Route::get('/user-info', function (Request $request) {
    if (auth()->check()) {
        return response()->json([
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email
        ]);
    }
    return response()->json(['error' => 'Not authenticated'], 401);
});
