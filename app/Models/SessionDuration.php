<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionDuration extends Model
{
    use HasFactory;

    protected $fillable = ['session_duration'];

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'session_duration_id', 'id');
    }
}
