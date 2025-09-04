<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function md_sessions()
    {
        return $this->hasMany(MdSession::class);
    }
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'md_sessions');
    }
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
