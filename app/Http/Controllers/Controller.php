<?php

namespace App\Http\Controllers;

abstract class BaseController
{

    /**
     * Apply common middleware.
     *
     * @return void
     */
    protected function middleware()
    {
        // Implement common middleware logic here
    }
}
