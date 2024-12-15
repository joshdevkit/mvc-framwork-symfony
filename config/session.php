<?php

return [
    'driver' => 'file',
    'path' => __DIR__ . '/../storage/framework/sessions',
    'lifetime' => 3600, // Session lifetime in minutes (1 hour)
    'expire_on_close' => true, // Expire the session when the browser closes
    'secure' => env('SESSION_SECURE_COOKIE', false), // Only send cookies over HTTPS
    'httponly' => true,  // Prevent access to cookies via JavaScript
    'same_site' => 'lax', // Options: 'lax', 'strict', 'none'
];
