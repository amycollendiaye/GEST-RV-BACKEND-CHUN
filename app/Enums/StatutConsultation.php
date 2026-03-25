<?php

namespace App\Enums;

enum StatutConsultation: string
{
    case PLANIFIER = 'PLANIFIER';
    case FAIT = 'FAIT';
    case ANNULER = 'ANNULER';
}
