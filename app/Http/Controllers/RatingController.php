<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingResource;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function doctor_ratings($doctor_id)
    {
        $doctor = Doctor::find($doctor_id);
        $ratings = $doctor->ratings;

        return RatingResource::collection($ratings);
    }
    public function my_ratings()
    {
        $current_user = Auth::user();
        if ($current_user->role == User::ROLE_DOCTOR) {
            $user = Auth::user()->doctor;
        } else {
            $user = Auth::user()->patient;
        }
        $ratings = $user->ratings;

        return RatingResource::collection($ratings);
    }
}
