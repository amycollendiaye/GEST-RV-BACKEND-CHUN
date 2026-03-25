<?php

namespace App\Exceptions;

use Exception;

class PlanningNonModifiableException extends Exception
{
    public function __construct()
    {
        parent::__construct('Le planning ne peut pas etre modifie car des rendez-vous ont deja ete attribues.', 422);
    }
}
