<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{
    /**
     * Get the HTTP method (GET, POST, PUT, DELETE, etc.).
     */
    public function getMethod(): string
    {
        return $this->server->get('REQUEST_METHOD');
    }

    /**
     * Get the current URL path (e.g., "/about").
     */
    public function getPath(): string
    {
        return $this->getRequestUri();
    }

    /**
     * Create a Request instance from PHP globals.
     */
    public static function createFromGlobals(): self
    {
        return self::create(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
    }
}
