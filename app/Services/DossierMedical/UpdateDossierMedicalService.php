<?php

namespace App\Services\DossierMedical;

use App\Repositories\Interfaces\DossierMedicalRepositoryInterface;

class UpdateDossierMedicalService
{
    public function __construct(
        private readonly DossierMedicalRepositoryInterface $dossierMedicalRepository
    ) {
    }

    public function execute(string $id, array $data)
    {
        $allowed = [
            'groupe_sanguin',
            'antecedents_medicaux',
            'antecedents_chirurgicaux',
            'antecedents_familiaux',
            'allergies',
            'maladies_chroniques',
            'traitements_en_cours',
        ];

        $payload = array_intersect_key($data, array_flip($allowed));

        return $this->dossierMedicalRepository->update($id, $payload);
    }
}
