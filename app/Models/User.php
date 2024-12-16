<?php

namespace App\Models;

use App\Core\Models;

class User extends Models
{
    // attribute of users table
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar'
    ];

    /**
     * Apply cast to the password as hidden
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
    /**
     * apply cast to the password as hashed
     *
     * @var array
     */
    protected $casts = [
        'password' => 'hashed',
    ];
}
