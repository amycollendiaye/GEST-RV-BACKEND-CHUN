<?php

namespace App\Http\Requests\DossierMedical;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDossierMedicalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'groupe_sanguin' => 'sometimes|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'antecedents_medicaux' => 'sometimes|string|max:2000',
            'antecedents_chirurgicaux' => 'sometimes|string|max:2000',
            'antecedents_familiaux' => 'sometimes|string|max:2000',
            'allergies' => 'sometimes|string|max:1000',
            'maladies_chroniques' => 'sometimes|string|max:1000',
            'traitements_en_cours' => 'sometimes|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'groupe_sanguin.in' => 'Le groupe sanguin est invalide.',
            'antecedents_medicaux.string' => 'Les antécédents médicaux doivent être une chaîne de caractères.',
            'antecedents_medicaux.max' => 'Les antécédents médicaux ne doivent pas dépasser 2000 caractères.',
            'antecedents_chirurgicaux.string' => 'Les antécédents chirurgicaux doivent être une chaîne de caractères.',
            'antecedents_chirurgicaux.max' => 'Les antécédents chirurgicaux ne doivent pas dépasser 2000 caractères.',
            'antecedents_familiaux.string' => 'Les antécédents familiaux doivent être une chaîne de caractères.',
            'antecedents_familiaux.max' => 'Les antécédents familiaux ne doivent pas dépasser 2000 caractères.',
            'allergies.string' => 'Les allergies doivent être une chaîne de caractères.',
            'allergies.max' => 'Les allergies ne doivent pas dépasser 1000 caractères.',
            'maladies_chroniques.string' => 'Les maladies chroniques doivent être une chaîne de caractères.',
            'maladies_chroniques.max' => 'Les maladies chroniques ne doivent pas dépasser 1000 caractères.',
            'traitements_en_cours.string' => 'Les traitements en cours doivent être une chaîne de caractères.',
            'traitements_en_cours.max' => 'Les traitements en cours ne doivent pas dépasser 1000 caractères.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $fields = [
                'groupe_sanguin',
                'antecedents_medicaux',
                'antecedents_chirurgicaux',
                'antecedents_familiaux',
                'allergies',
                'maladies_chroniques',
                'traitements_en_cours',
            ];

            $hasAny = false;
            foreach ($fields as $field) {
                if ($this->has($field)) {
                    $hasAny = true;
                    break;
                }
            }

            if (!$hasAny) {
                $validator->errors()->add('message', 'Au moins un champ doit être renseigné.');
            }
        });
    }
}
