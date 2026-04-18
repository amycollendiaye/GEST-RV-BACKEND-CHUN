<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $formattedErrors = [];

        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = is_array($messages) && count($messages) > 0
                ? $messages[0]
                : $messages;
        }

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors'  => $formattedErrors,
            ], 422)
        );
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $hash = hash('sha256', strtolower(trim($value)));
                    $exists = Patient::where('email_hash', $hash)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        $fail('Cet email est déjà utilisé.');
                    }
                },
            ],
            'telephone' => [
                'required',
                'regex:/^\d{9}$/',
                function ($attribute, $value, $fail) {
                    $hash = hash('sha256', preg_replace('/\D+/', '', $value));
                    $exists = Patient::where('telephone_hash', $hash)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        $fail('Ce numéro de téléphone est déjà utilisé.');
                    }
                },
            ],
            'dateNaissance' => 'required|date|before:today',
            'adresse' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 100 caractères.',
            'email.required' => 'L’email est obligatoire.',
            'email.email' => 'L’email doit être une adresse valide.',
            'telephone.required' => 'Le téléphone est obligatoire.',
            'telephone.regex' => 'Le téléphone doit contenir exactement 9 chiffres.',
            'dateNaissance.required' => 'La date de naissance est obligatoire.',
            'dateNaissance.date' => 'La date de naissance doit être une date valide.',
            'dateNaissance.before' => 'La date de naissance doit être antérieure à aujourd’hui.',
            'adresse.required' => 'L’adresse est obligatoire.',
            'adresse.string' => 'L’adresse doit être une chaîne de caractères.',
            'adresse.max' => 'L’adresse ne doit pas dépasser 255 caractères.',
        ];
    }
}
