<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Name extends Model
{
    use HasFactory;

    public function people()
    {
        return $this->hasMany(Person::class, 'person_id', 'id');
    }
}
