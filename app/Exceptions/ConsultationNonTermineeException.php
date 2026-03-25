<?php

namespace App\Exceptions;

use Exception;

class ConsultationNonTermineeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Le patient doit d\'abord effectuer la consultation de son rendez-vous existant dans ce service avant d\'en créer un nouveau.', 409);
    }
}
