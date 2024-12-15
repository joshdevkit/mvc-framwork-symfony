<?php

namespace App\Core;

use App\Core\Redirector;
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
            session(['errors' => ['email' => ['No matching record found with the credentials provided']]]);
            Redirector::back()->send();
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
     * Return an instance of Auth (singleton-like).
     *
     * @return self
     */
    public static function instance(): self
    {
        return new self();
    }

    /**
     * Get the authenticated user.
     *
     * @return User|null
     */
    public function user(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return User::find((int) session('user_id'));
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
    public static function destroy(): void
    {
        session_forget('user_id');
    }
}
