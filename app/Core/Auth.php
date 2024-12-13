<?php

namespace App\Core;

use App\Models\User;
use Exception;

class Auth
{
    /**
     * Attempt to authenticate a user with provided credentials.
     *
     * @param string $email
     * @param string $password
     * @return bool
     * @throws Exception
     */
    public static function attempt(string $email, string $password): bool
    {
        /**
         * Call the User Model for instance
         * @var App\Models\User;
         */
        $user = User::findByEmail($email);
        if (!$user) {
            throw new Exception("User not found.");
        }
        // Verify the password (Use the password_verify as we use the hash class on create)
        if (!password_verify($password, $user->password)) {
            return false;
        }
        // Store the authenticated user in the session
        $_SESSION['user_id'] = $user->id;

        return true;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return User|null
     */
    public static function user(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return User::find((int) $_SESSION['user_id']);
    }

    /**
     * Check if a user is authenticated.
     *
     * @return bool
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Log out the authenticated user.
     *
     * @return void
     */
    public static function logout(): void
    {
        unset($_SESSION['user_id']);
    }
}
