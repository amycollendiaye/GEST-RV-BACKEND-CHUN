<?php

namespace App\Exceptions;

use Exception;

class RendezVousDejaExistantException extends Exception
{
    public function __construct()
    {
        parent::__construct('Le patient possède déjà un rendez-vous en attente dans ce service.', 409);
    }
}
