<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DoctorController;
use Illuminate\Support\Facades\Route;

// Route publik (tidak perlu login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);
Route::get('/doctors/{id}/available-slots', [DoctorController::class, 'availableSlots']);

// Route yang butuh login (pakai token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::post('/bookings/{id}/pay', [PaymentController::class, 'createSnapToken']);

    // Khusus pasien
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/my-bookings', [BookingController::class, 'myBookings']);
    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Khusus dokter
    Route::get('/doctor/bookings', [BookingController::class, 'doctorBookings']);
    Route::patch('/doctor/bookings/{id}/status', [BookingController::class, 'updateStatus']);
});
  Route::post('/payment/notification', [PaymentController::class, 'handleNotification']);