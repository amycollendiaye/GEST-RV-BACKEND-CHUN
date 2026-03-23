<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceMedicalRequest extends FormRequest
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
        $serviceId = $this->route('serviceMedical') ?? $this->route('id');

        return [
            'nom' => 'sometimes|required|string|max:150',
            'nom_hash' => [
                'sometimes',
                'required',
                Rule::unique('service_medicals', 'nom_hash')->ignore($serviceId),
            ],
            'description' => 'nullable|string|max:500',
            'heure_ouverture' => 'sometimes|required|date_format:H:i',
            'heure_fermeture' => 'sometimes|required|date_format:H:i|after:heure_ouverture',
            'etat' => 'nullable|in:DISPONIBLE,INDISPONIBLE',
        ];
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
