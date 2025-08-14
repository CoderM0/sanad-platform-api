<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $guarded = [];
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
