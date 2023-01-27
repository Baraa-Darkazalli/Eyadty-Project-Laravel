<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use HasFactory;

    public function people()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }
}
