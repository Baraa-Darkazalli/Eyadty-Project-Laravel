<?php

namespace App\Models;

use Laratrust\Models\LaratrustRole;

class Role extends LaratrustRole
{
    public $guarded = [];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_id', 'permission_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_id', 'user_id');
    }
}
