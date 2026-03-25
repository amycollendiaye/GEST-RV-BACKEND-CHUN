<?php

namespace App\Http\Requests\PlanningMedecin;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after:today'],
            'heure_ouverture' => ['required', 'date_format:H:i'],
            'heure_fermeture' => ['required', 'date_format:H:i', 'after:heure_ouverture'],
            'capacite' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'La date du planning est obligatoire.',
            'date.date' => 'La date du planning est invalide.',
            'date.after' => 'La date du planning doit etre dans le futur.',
            'heure_ouverture.required' => 'L heure d ouverture est obligatoire.',
            'heure_ouverture.date_format' => 'L heure d ouverture doit etre au format HH:MM.',
            'heure_fermeture.required' => 'L heure de fermeture est obligatoire.',
            'heure_fermeture.date_format' => 'L heure de fermeture doit etre au format HH:MM.',
            'heure_fermeture.after' => 'L heure de fermeture doit etre strictement superieure a l heure d ouverture.',
            'capacite.required' => 'La capacite est obligatoire.',
            'capacite.integer' => 'La capacite doit etre un entier.',
            'capacite.min' => 'La capacite doit etre au minimum de 1.',
            'capacite.max' => 'La capacite ne doit pas depasser 50.',
        ];
    }
}
