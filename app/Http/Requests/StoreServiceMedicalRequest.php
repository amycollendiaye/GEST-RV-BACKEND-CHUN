<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StoreServiceMedicalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:150',
            'nom_hash' => ['required', Rule::unique('service_medicals', 'nom_hash')],
            'description' => 'nullable|string|max:500',
            'heure_ouverture' => 'required|date_format:H:i',
            'heure_fermeture' => 'required|date_format:H:i|after:heure_ouverture',
            'etat' => 'nullable|in:DISPONIBLE,INDISPONIBLE',
        ];
    }
     protected function failedValidation(Validator $validator)
    {
        // Transformer les erreurs pour obtenir seulement le premier message (sans tableau)
        $errors          = $validator->errors()->toArray();
        $formattedErrors = [];

        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = is_array($messages) && count($messages) > 0
                ? $messages[0]
                : $messages;
            Log::info('Validation error', [
                'field' => $field,
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
        if ($this->has('nom')) {
            $nom = (string) $this->input('nom');
            $this->merge([
                'nom_hash' => hash('sha256', strtolower(trim($nom))),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 150 caractères.',
            'nom_hash.unique' => 'Ce nom de service est déjà utilisé.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
            'heure_ouverture.required' => 'L\'heure d\'ouverture est obligatoire.',
            'heure_ouverture.date_format' => 'Le format de l\'heure d\'ouverture est invalide (HH:MM).',
            'heure_fermeture.required' => 'L\'heure de fermeture est obligatoire.',
            'heure_fermeture.date_format' => 'Le format de l\'heure de fermeture est invalide (HH:MM).',
            'heure_fermeture.after' => 'L\'heure de fermeture doit être après l\'heure d\'ouverture.',
            'etat.in' => 'L\'état doit être DISPONIBLE ou INDISPONIBLE.',
        ];
    }
}
