<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\JsonResponse;

class Response
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
}
