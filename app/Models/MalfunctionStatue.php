<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MalfunctionStatue extends Model
{
    use HasFactory;

    public function malfunction()
    {
        return $this->hasOne(Malfunction::class, 'malfunction_statue_id', 'id');
    }
}
