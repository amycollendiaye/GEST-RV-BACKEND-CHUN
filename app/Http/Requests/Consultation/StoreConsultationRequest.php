<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rendez_vous_id' => 'required|exists:rendez_vous,id',
            'tension_artielle' => 'required|string|max:20',
            'poids' => 'required|numeric|gt:0',
            'temperature' => 'required|numeric|between:30,45',
            'sumptomes' => 'required|string|max:1000',
            'diagnostic' => 'required|string|max:1000',
            'traitement' => 'required|string|max:1000',
            'observations' => 'sometimes|string|max:1000',
            'mise_a_jour_dossier' => 'sometimes|array',
            'mise_a_jour_dossier.maladies_chroniques' => 'sometimes|string|max:1000',
            'mise_a_jour_dossier.traitements_en_cours' => 'sometimes|string|max:1000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tension_artielle' => $this->input('tension_artielle', $this->input('tensionArtielle')),
        ]);
    }

    public function messages(): array
    {
        return [
            'rendez_vous_id.required' => 'Le rendez-vous est obligatoire.',
            'rendez_vous_id.exists' => 'Le rendez-vous sélectionné est invalide.',
            'tension_artielle.required' => 'La tension artérielle est obligatoire.',
            'tension_artielle.string' => 'La tension artérielle doit être une chaîne de caractères.',
            'tension_artielle.max' => 'La tension artérielle ne doit pas dépasser 20 caractères.',
            'poids.required' => 'Le poids est obligatoire.',
            'poids.numeric' => 'Le poids doit être un nombre.',
            'poids.gt' => 'Le poids doit être positif.',
            'temperature.required' => 'La température est obligatoire.',
            'temperature.numeric' => 'La température doit être un nombre.',
            'temperature.between' => 'La température doit être comprise entre 30 et 45.',
            'sumptomes.required' => 'Les symptômes sont obligatoires.',
            'sumptomes.string' => 'Les symptômes doivent être une chaîne de caractères.',
            'sumptomes.max' => 'Les symptômes ne doivent pas dépasser 1000 caractères.',
            'diagnostic.required' => 'Le diagnostic est obligatoire.',
            'diagnostic.string' => 'Le diagnostic doit être une chaîne de caractères.',
            'diagnostic.max' => 'Le diagnostic ne doit pas dépasser 1000 caractères.',
            'traitement.required' => 'Le traitement est obligatoire.',
            'traitement.string' => 'Le traitement doit être une chaîne de caractères.',
            'traitement.max' => 'Le traitement ne doit pas dépasser 1000 caractères.',
            'observations.string' => 'Les observations doivent être une chaîne de caractères.',
            'observations.max' => 'Les observations ne doivent pas dépasser 1000 caractères.',
            'mise_a_jour_dossier.array' => 'La mise à jour du dossier doit être un tableau.',
            'mise_a_jour_dossier.maladies_chroniques.string' => 'Les maladies chroniques doivent être une chaîne de caractères.',
            'mise_a_jour_dossier.maladies_chroniques.max' => 'Les maladies chroniques ne doivent pas dépasser 1000 caractères.',
            'mise_a_jour_dossier.traitements_en_cours.string' => 'Les traitements en cours doivent être une chaîne de caractères.',
            'mise_a_jour_dossier.traitements_en_cours.max' => 'Les traitements en cours ne doivent pas dépasser 1000 caractères.',
        ];
    }
}
