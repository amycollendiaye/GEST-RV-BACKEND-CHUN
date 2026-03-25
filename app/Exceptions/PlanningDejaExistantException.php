<?php

namespace App\Exceptions;

use Exception;

class PlanningDejaExistantException extends Exception
{
    public function __construct()
    {
        parent::__construct('Un planning existe deja pour cette date dans ce service.', 409);
    }
}
