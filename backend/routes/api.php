<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\RatingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Routes protégées (JWT)
Route::middleware('auth:api')->group(function () {
    
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // User Profile
    Route::prefix('user')->group(function () {
        Route::get('/dashboard', [UserController::class, 'dashboard']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/profile', [UserController::class, 'update']);
        Route::post('/photo', [UserController::class, 'updatePhoto']);
        Route::put('/password', [UserController::class, 'updatePassword']);
    });

    // Trips
    Route::prefix('trips')->group(function () {
        Route::get('/', [TripController::class, 'index']);
        Route::post('/', [TripController::class, 'store']);
        Route::get('/my-trips', [TripController::class, 'myTrips']);
        Route::get('/{id}', [TripController::class, 'show']);
        Route::put('/{id}', [TripController::class, 'update']);
        Route::delete('/{id}', [TripController::class, 'destroy']);
    });

    // Bookings
    Route::prefix('bookings')->group(function () {
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/my-bookings', [BookingController::class, 'myBookings']);
        Route::get('/pending', [BookingController::class, 'pendingBookings']);
        Route::post('/{id}/confirm', [BookingController::class, 'confirm']);
        Route::post('/{id}/refuse', [BookingController::class, 'refuse']);
        Route::post('/{id}/cancel', [BookingController::class, 'cancel']);
    });

    // Messages
    Route::prefix('messages')->group(function () {
        Route::get('/templates', [MessageController::class, 'templates']);
        Route::get('/unread-count', [MessageController::class, 'unreadCount']);
        Route::get('/booking/{bookingId}', [MessageController::class, 'index']);
        Route::post('/booking/{bookingId}', [MessageController::class, 'store']);
    });

    // Ratings
    Route::prefix('ratings')->group(function () {
        Route::post('/booking/{bookingId}', [RatingController::class, 'store']);
        Route::get('/pending', [RatingController::class, 'pendingRatings']);
        Route::get('/user/{userId}', [RatingController::class, 'userRatings']);
    });
});

// Route de test
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'CO-CESI API is running',
        'timestamp' => now()
    ]);
});
