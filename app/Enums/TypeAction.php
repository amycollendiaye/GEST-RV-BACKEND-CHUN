<?php

namespace App\Enums;

enum TypeAction: string
{
    case CONNEXION = 'CONNEXION';
    case DECONNEXION = 'DECONNEXION';
    case REPROG = 'REPROG';
    case CREATIONRV = 'CREATIONRV';
    case CREATIONDOSSIER = 'CREATIONDOSSIER';
    case ENRCONSUL = 'ENRCONSUL';
    case ANNULERRV = 'ANNULERRV';
    case CREATIONPERSONNEL = 'CREATIONPERSONNEL';
    case MODIFICATIONPERSONNEL = 'MODIFICATIONPERSONNEL';
    case SUPPRESSIONPERSONNEL = 'SUPPRESSIONPERSONNEL';
    case MODIFICATIONPATIENT = 'MODIFICATIONPATIENT';
    case CLOTURECONSUL = 'CLOTURECONSUL';

    public static function values(): array
    {
        return array_map(static fn (self $type) => $type->value, self::cases());
    }
}
