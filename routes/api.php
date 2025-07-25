<?php

use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\MdSessionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Patient\PatientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'role:patient'])->prefix("/patient")->group(function () {
    Route::get('/home', [PatientController::class, 'home']);
    Route::post('/update', [PatientController::class, 'update']);
    Route::post('/reserve', [MdSessionController::class, 'store']);
    Route::get('/doctor/{doctorId}/free', [MdSessionController::class, 'free_times']);
    Route::get('/sessions', [PatientController::class, 'my_sessions']);
    Route::get('/doctors', [PatientController::class, 'doctors']);
});

Route::middleware(['auth:sanctum'])->controller(NotificationController::class)->group(function () {
    Route::get('/notifications', 'get_user_notifications');
    Route::post('/notifications/{id}/read', 'mark_as_read');
    Route::post('/notifications/read-all', "read_all");
    Route::delete('/notifications/{id}', 'delete_notification');
    Route::delete('/notifications', 'delete_all_notifications');
    Route::get('/notifications/unread', 'unread_notifications');
});

Route::middleware(['auth:sanctum', 'role:doctor'])->prefix("/doctor")->group(function () {
    Route::get('/sessions', [DoctorController::class, 'my_sessions']);
});












require __DIR__ . '/auth.php';
