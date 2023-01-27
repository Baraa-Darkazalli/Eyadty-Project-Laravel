<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = ['subject', 'body', 'user_id'];

    use HasFactory;

    public static function log($subject, $body, $user_id)
    {
        Log::create(['subject' => $subject, 'body' => $body, 'user_id' => $user_id]);
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
