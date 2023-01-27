<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    public function certificate_source()
    {
        return $this->hasMany(CertificateSource::class, 'country_id', 'id');
    }

    public function previous_work()
    {
        return $this->hasOne(PreviousWork::class, 'country_id', 'id');
    }
}
