<?php

namespace App\Exceptions;

use Exception;

class AucunCreneauDisponibleException extends Exception
{
    public function __construct()
    {
        parent::__construct('Aucun creneau disponible n est trouve dans ce service pour le moment. Veuillez reessayer ulterieurement.', 404);
    }
}
