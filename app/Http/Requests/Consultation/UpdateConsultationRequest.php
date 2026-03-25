<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tensionArtielle' => 'sometimes|string|max:20',
            'poids' => 'sometimes|numeric|gt:0',
            'temperature' => 'sometimes|numeric|between:30,45',
            'sumptomes' => 'sometimes|string|max:1000',
            'diagnostic' => 'sometimes|string|max:1000',
            'traitement' => 'sometimes|string|max:1000',
            'observations' => 'sometimes|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'tensionArtielle.string' => 'La tension artérielle doit être une chaîne de caractères.',
            'tensionArtielle.max' => 'La tension artérielle ne doit pas dépasser 20 caractères.',
            'poids.numeric' => 'Le poids doit être un nombre.',
            'poids.gt' => 'Le poids doit être positif.',
            'temperature.numeric' => 'La température doit être un nombre.',
            'temperature.between' => 'La température doit être comprise entre 30 et 45.',
            'sumptomes.string' => 'Les symptômes doivent être une chaîne de caractères.',
            'sumptomes.max' => 'Les symptômes ne doivent pas dépasser 1000 caractères.',
            'diagnostic.string' => 'Le diagnostic doit être une chaîne de caractères.',
            'diagnostic.max' => 'Le diagnostic ne doit pas dépasser 1000 caractères.',
            'traitement.string' => 'Le traitement doit être une chaîne de caractères.',
            'traitement.max' => 'Le traitement ne doit pas dépasser 1000 caractères.',
            'observations.string' => 'Les observations doivent être une chaîne de caractères.',
            'observations.max' => 'Les observations ne doivent pas dépasser 1000 caractères.',
        ];
    }
}
