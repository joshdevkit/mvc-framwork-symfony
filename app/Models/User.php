<?php

namespace App\Models;

use App\Core\Models;

class User extends Models
{
    protected $fillable = [
        'name',
        'email',
        'password'
    ];
}
