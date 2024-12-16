<?php

namespace App\Http\Controllers;

use App\Core\Traits\AuthorizesRequests;
use App\Core\Traits\ValidateRequest;

abstract class Controller
{
    use AuthorizesRequests,
        ValidateRequest;
}
