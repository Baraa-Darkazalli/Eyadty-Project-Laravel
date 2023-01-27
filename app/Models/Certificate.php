<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    public function certificate_name()
    {
        return $this->belongsTo(CertificateName::class, 'certificate_name_id', 'id');
    }

    public function certificate_source()
    {
        return $this->belongsTo(CertificateSource::class, 'certificate_source_id', 'id');
    }

    public function certificate_ratings()
    {
        return $this->belongsTo(CertificateRating::class, 'certificate_rating_id', 'id');
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
