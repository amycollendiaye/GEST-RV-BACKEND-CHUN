<?php

namespace App\Http\Requests\RendezVous;

use Illuminate\Foundation\Http\FormRequest;

class ReprogrammerRendezVousRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motif_suivi' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motif_suivi.required' => 'Le motif du suivi est obligatoire.',
            'motif_suivi.string' => 'Le motif du suivi doit etre une chaine de caracteres.',
            'motif_suivi.max' => 'Le motif du suivi ne doit pas depasser 500 caracteres.',
        ];
    }
}
