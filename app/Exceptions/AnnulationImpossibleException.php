<?php

namespace App\Exceptions;

use Exception;

class AnnulationImpossibleException extends Exception
{
    public function __construct()
    {
        parent::__construct('L\'annulation n\'est plus possible à moins de 24 heures du rendez-vous.', 422);
    }
}
