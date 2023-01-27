<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreviousWork extends Model
{
    use HasFactory;

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_id', 'id');
    }
}
