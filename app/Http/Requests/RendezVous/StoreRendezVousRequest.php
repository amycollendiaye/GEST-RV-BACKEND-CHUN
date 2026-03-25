<?php

namespace App\Http\Requests\RendezVous;

use Illuminate\Foundation\Http\FormRequest;

class StoreRendezVousRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_medical_id' => 'required|exists:service_medicals,id',
            'motif' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'service_medical_id.required' => 'Le service médical est obligatoire.',
            'service_medical_id.exists' => 'Le service médical sélectionné est invalide.',
            'motif.required' => 'Le motif est obligatoire.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'motif.max' => 'Le motif ne doit pas dépasser 500 caractères.',
        ];
    }
}
