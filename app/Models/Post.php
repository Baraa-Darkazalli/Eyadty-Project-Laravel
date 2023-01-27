<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'like_posts', 'post_id', 'user_id');
    }

    public function doctors()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function extra_services()
    {
        return $this->belongsTo(ExtraService::class, 'blog_id', 'id');
    }

    public function viewers()
    {
        return $this->belongsToMany(User::class, 'seen_posts', 'post_id', 'user_id');
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'like_posts', 'post_id', 'user_id');
    }
}
