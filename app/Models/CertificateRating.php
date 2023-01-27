<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateRating extends Model
{
    use HasFactory;

    protected $table = 'certificate_ratings';

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'certificate_rating_id', 'id');
    }
}
