<?php

namespace App\Http\Requests\PlanningMedecin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'required', 'date', 'after:today'],
            'heure_ouverture' => ['sometimes', 'required', 'date_format:H:i'],
            'heure_fermeture' => ['sometimes', 'required', 'date_format:H:i', 'after:heure_ouverture'],
            'capacite' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->all())) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'data' => null,
                'errors' => [
                    'request' => 'Au moins un champ doit etre renseigne.',
                ],
            ], 422));
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'data' => null,
            'errors' => $validator->errors(),
        ], 422));
    }

    public function messages(): array
    {
        return [
            'date.date' => 'La date du planning est invalide.',
            'date.after' => 'La date du planning doit etre dans le futur.',
            'heure_ouverture.date_format' => 'L heure d ouverture doit etre au format HH:MM.',
            'heure_fermeture.date_format' => 'L heure de fermeture doit etre au format HH:MM.',
            'heure_fermeture.after' => 'L heure de fermeture doit etre strictement superieure a l heure d ouverture.',
            'capacite.integer' => 'La capacite doit etre un entier.',
            'capacite.min' => 'La capacite doit etre au minimum de 1.',
            'capacite.max' => 'La capacite ne doit pas depasser 50.',
        ];
    }
}
