<?php

use App\Http\Controllers\MdSessionController;
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
});















require __DIR__ . '/auth.php';
