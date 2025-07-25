<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\MdSessionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    public function my_sessions()
    {
        $doctor = Auth::user()->doctor;
        $md_session = $doctor->md_sessions;

        return MdSessionResource::collection($md_session);
    }
}
