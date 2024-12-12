<?php

namespace App\Core;

class Hash
{
    /**
     * Hash the given password using BCRYPT.
     *
     * @param string $password
     * @return string
     */
    public static function make(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a given password against a hash.
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function check(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
