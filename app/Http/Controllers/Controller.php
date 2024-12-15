<?php

namespace App\Http\Controllers;

use App\Core\Request;

abstract class Controller
{
    protected $request;

    public function __construct()
    {
        // Perform global request validation
        $this->validateRequest();
        //inherit the Request createFromGlobals
        $this->request = Request::createFromGlobals();
    }


    /**  Validate the request data against the given rules. 
     *
     * @param array $rules 
     * @return array 
     */
    protected function validate(array $rules): array
    {
        return $this->request->validate($rules);
    }

    protected function validateRequest(): void
    {
        $request = Request::createFromGlobals();
        // Check if the request is AJAX
        if ($request->isXmlHttpRequest()) {
            // Validate CSRF token
            if (!$this->isValidCsrfToken($request)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
        }
    }

    protected function isValidCsrfToken(Request $request): bool
    {
        $csrfToken = $request->header('X-CSRF-TOKEN');
        $sessionToken = $_SESSION['csrf_tokens']['_token'] ?? null;

        return $csrfToken && $csrfToken === $sessionToken;
    }
}
