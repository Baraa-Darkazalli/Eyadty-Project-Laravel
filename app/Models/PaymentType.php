<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    protected $table = 'payment_types';

    public function balance_payment()
    {
        return $this->hasOne(BalancePayment::class, 'payement_type_id', 'id');
    }
}
