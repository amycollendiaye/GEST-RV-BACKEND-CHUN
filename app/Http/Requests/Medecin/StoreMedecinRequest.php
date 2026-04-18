<?php

namespace App\Http\Requests\Medecin;

use App\Rules\Telephone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StoreMedecinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email',
            'email_hash' => ['required', Rule::unique('personel_hopitals', 'email_hash')->whereNull('deleted_at')],
            "telephone" => ['required', 'string', new Telephone()],
            'telephone_hash' => ['required', Rule::unique('personel_hopitals', 'telephone_hash')->whereNull('deleted_at')],
            'specialite' => 'required|string|max:100',
            'service_medical_id' => 'required|exists:service_medicals,id',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        // Transformer les erreurs pour obtenir seulement le premier message (sans tableau)
        $errors          = $validator->errors()->toArray();
        $formattedErrors = [];

        foreach ($errors as $field => $messages) {
            $key = $field;
            if ($field === 'email_hash') $key = 'email';
            if ($field === 'telephone_hash') $key = 'telephone';

            $formattedErrors[$key] = is_array($messages) && count($messages) > 0
                ? $messages[0]
                : $messages;
            
            Log::info('Validation error', [
                'field' => $field,
                'mapped_to' => $key,
                'messages' => $messages,
            ]);
        }

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors'  => $formattedErrors,
            ], 422)
        );
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nom' => $this->nom ? trim($this->nom) : null,
            'prenom' => $this->prenom ? trim($this->prenom) : null,
            'email' => $this->email ? trim($this->email) : null,
            'email_hash' => $this->email ? hash('sha256', strtolower(trim($this->email))) : null,
            'telephone_hash' => $this->telephone
                ? hash('sha256', preg_replace('/\\D+/', '', $this->telephone))
                : null,
        ]);
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
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'Le format de l\'email est invalide.',
            'email_hash.unique' => 'Cet email est déjà utilisé.',
            'telephone.required' => 'Le téléphone est obligatoire.',
            'telephone.regex' => 'Le téléphone doit contenir exactement 9 chiffres.',
            'telephone_hash.unique' => 'Ce téléphone est déjà utilisé.',
            'specialite.required' => 'La spécialité est obligatoire.',
            'specialite.string' => 'La spécialité doit être une chaîne de caractères.',
            'specialite.max' => 'La spécialité ne doit pas dépasser 100 caractères.',
            'service_medical_id.required' => 'Le service médical est obligatoire.',
            'service_medical_id.exists' => 'Le service médical sélectionné est invalide.',
        ];
    }
}
