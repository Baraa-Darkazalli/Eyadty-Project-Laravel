<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateSource extends Model
{
    use HasFactory;

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'certificate_source_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}
