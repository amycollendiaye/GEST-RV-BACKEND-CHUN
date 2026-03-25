<?php

namespace App\Exceptions;

use Exception;

class DossierMedicalModificationInterditeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Seul le médecin peut modifier le dossier médical.', 403);
    }
}
