<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\MdSessionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\RatingController;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Models\ContactInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'role:patient'])->prefix("/patient")->group(function () {
    Route::get('/home', [PatientController::class, 'home']);
    Route::post('/update', [PatientController::class, 'update']);
    Route::post('/reserve', [MdSessionController::class, 'store']);
    Route::get('/sessions', [PatientController::class, 'my_sessions']);
    Route::get('/doctors', [PatientController::class, 'doctors']);
    Route::post("/ratings/add", [PatientController::class, 'add_rate']);
    Route::get('/ratings', [RatingController::class, 'my_ratings']);
    Route::post('/tests/store', [PatientController::class, 'storeTestResults']);
    Route::get('/tests/{test_id}/show', [PatientController::class, 'getTestResults']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/doctor/{doctor_id}/ratings', [RatingController::class, 'doctor_ratings']);
    Route::post('/doctor/{doctorId}/free', [MdSessionController::class, 'free_times']);
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
    Route::get('/ratings', [RatingController::class, 'my_ratings']);
    Route::post('/update', [DoctorController::class, 'update_info']);
    Route::post('/sessions/{session_id}/accept', [DoctorController::class, 'accept_session']);
    Route::post('/sessions/{session_id}/decline', [DoctorController::class, 'decline_session']);
    Route::post('/sessions/{session_id}/modify', [DoctorController::class, 'change_session_time']);
    Route::get('/patients', [DoctorController::class, 'my_patients']);
    Route::get('/patients/{patient_id}/{test_id}/show', [DoctorController::class, 'getTestResults']);
    Route::post("/blogs/add", [DoctorController::class, 'add_blog']);
    Route::post("/blogs/{blog_id}/sections/add", [DoctorController::class, 'add_section']);
    Route::get("/blogs/{blog}/sections/get", [DoctorController::class, 'get_blog_info']);
});
Route::get("/contacts/all", function () {
    return response()->json(['contacts' => ContactInfo::all()]);
});
Route::get("/blogs/all", function () {
    return BlogResource::collection(Blog::all());
});
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('/admin')->group(function () {
    Route::get("/doctors", [AdminController::class, 'get_all_doctors']);
    Route::post("/doctors/add", [AdminController::class, 'add_doctor']);
    Route::post("/doctors/{doctor_id}/update", [AdminController::class, 'update_doctor']);
    Route::delete("/doctors/{doctor_id}/delete", [AdminController::class, 'delete_doctor']);
    Route::get("/doctors/{doctor_id}/patients", [AdminController::class, 'doc_patients']);
    Route::get("/doctors/patients", [AdminController::class, 'docs_with_patients']);
    Route::post("/contacts/add", [AdminController::class, 'add_contact']);
    Route::put("/contacts/{contact}/update", [AdminController::class, 'edit_contact']);
    Route::delete("/contacts/{contact}/delete", [AdminController::class, 'delete_contact']);
    Route::get("/finance", [AdminController::class, 'get_financial_records']);
    Route::post("/profile/update", [AdminController::class, 'update_info']);
    Route::get("/patients/all", [AdminController::class, 'get_all_patients']);
    Route::get("/sessions/all", [AdminController::class, 'get_all_sessions']);
});






require __DIR__ . '/auth.php';
