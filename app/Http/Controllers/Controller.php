<?php

namespace App\Http\Controllers;

use App\Core\Request;

abstract class Controller
{
    protected $request;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    /**
     * Apply common middleware.
     *
     * @return void
     */
    protected function middleware()
    {
        // Implement common middleware logic here
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
}
