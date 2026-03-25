<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->route('id') ?? $this->route('patient');

        return [
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'email' => [
                'sometimes',
                'email',
                function ($attribute, $value, $fail) use ($patientId) {
                    $hash = hash('sha256', strtolower(trim($value)));
                    $exists = Patient::where('email_hash', $hash)
                        ->where('id', '!=', $patientId)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        $fail('Cet email est déjà utilisé.');
                    }
                },
            ],
            'telephone' => [
                'sometimes',
                'regex:/^\d{9}$/',
                function ($attribute, $value, $fail) use ($patientId) {
                    $hash = hash('sha256', preg_replace('/\D+/', '', $value));
                    $exists = Patient::where('telephone_hash', $hash)
                        ->where('id', '!=', $patientId)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        $fail('Ce numéro de téléphone est déjà utilisé.');
                    }
                },
            ],
            'dateNaissance' => 'sometimes|date|before:today',
            'adresse' => 'sometimes|string|max:255',
            'statut' => 'sometimes|in:ACTIF,INACTIF',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 100 caractères.',
            'email.email' => 'L’email doit être une adresse valide.',
            'telephone.regex' => 'Le téléphone doit contenir exactement 9 chiffres.',
            'dateNaissance.date' => 'La date de naissance doit être une date valide.',
            'dateNaissance.before' => 'La date de naissance doit être antérieure à aujourd’hui.',
            'adresse.string' => 'L’adresse doit être une chaîne de caractères.',
            'adresse.max' => 'L’adresse ne doit pas dépasser 255 caractères.',
            'statut.in' => 'Le statut doit être ACTIF ou INACTIF.',
        ];
    }
}
