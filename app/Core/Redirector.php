<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Redirector
{
    /**
     * Return a JSON response.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return JsonResponse
     */
    public static function json(array $data, int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return new JsonResponse($data, $status, $headers, $options);
    }
    /**
     * Redirect to a URL.
     *
     * @param string $url
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    public static function to(string $url, int $status = 302, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * Redirect back with optional session data.
     *
     * @param array $data
     * @return RedirectResponse
     */
    public static function back(array $data = []): RedirectResponse
    {
        $backUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        if (!empty($data)) {
            $_SESSION['errors'] = $data;
        }
        return self::to($backUrl);
    }
}
