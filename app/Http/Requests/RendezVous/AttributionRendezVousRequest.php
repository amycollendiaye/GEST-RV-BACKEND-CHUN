<?php

namespace App\Http\Requests\RendezVous;

use Illuminate\Foundation\Http\FormRequest;

class AttributionRendezVousRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_medical_id' => ['required', 'exists:service_medicals,id'],
            'motif' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_medical_id.required' => 'Le service medical est obligatoire.',
            'service_medical_id.exists' => 'Le service medical selectionne est invalide.',
            'motif.required' => 'Le motif est obligatoire.',
            'motif.string' => 'Le motif doit etre une chaine de caracteres.',
            'motif.max' => 'Le motif ne doit pas depasser 500 caracteres.',
        ];
    }
}
