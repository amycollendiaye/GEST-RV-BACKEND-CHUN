<?php

namespace App\Http\Requests\Medecin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedecinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'sometimes|required|string|max:100',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('personel_hopitals', 'email_hash')->whereNull('deleted_at')->ignore($id),
            ],
            'email_hash' => [
                'sometimes',
                'required',
                Rule::unique('personel_hopitals', 'email_hash')->whereNull('deleted_at')->ignore($id),
            ],
            'telephone' => [
                'sometimes',
                'required',
                'string',
                'regex:/^[0-9]{9}$/',
                Rule::unique('personel_hopitals', 'telephone_hash')->whereNull('deleted_at')->ignore($id),
            ],
            'telephone_hash' => [
                'sometimes',
                'required',
                Rule::unique('personel_hopitals', 'telephone_hash')->whereNull('deleted_at')->ignore($id),
            ],
            'specialite' => 'sometimes|required|string|max:100',
            'service_medical_id' => 'sometimes|required|exists:service_medicals,id',
        ];
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
