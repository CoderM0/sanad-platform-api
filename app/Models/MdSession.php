<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MdSession extends Model
{
    protected $fillable = ['patient_id', 'doctor_id', 'scheduled_at', 'status'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
