<?php

namespace App\Models;

use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
    public $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_id', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_id', 'role_id');
    }
}
