<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateName extends Model
{
    use HasFactory;

    protected $table = 'certificate_names';

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'certificate_name_id', 'id');
    }
}
