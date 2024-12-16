<?php


namespace App\Core\Traits;

use App\Core\Request;


trait ValidateRequest
{
    protected $request;

    public function __construct()
    {
        // Perform global request validation
        $this->validateRequest();
        //inherit the Request createFromGlobals
        $this->request = Request::createFromGlobals();
    }


    /**
     * Validate the given request with the given rules.
     ** @param  string  $errorBag
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $attributes
     * @return array
     * @param  \App\Core\Request|null  $request
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
