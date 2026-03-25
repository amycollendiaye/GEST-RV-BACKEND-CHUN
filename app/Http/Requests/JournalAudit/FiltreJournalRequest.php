<?php

namespace App\Http\Requests\JournalAudit;

use App\Enums\TypeAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltreJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_action' => ['nullable', Rule::in(TypeAction::values())],
            'personel_id' => ['nullable', 'uuid', 'exists:personel_hopitals,id'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'adresse_ip' => ['nullable', 'string', 'max:45'],
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'sort_by' => ['nullable', Rule::in(['created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    public function messages(): array
    {
        return [
            'type_action.in' => 'Le type d\'action sélectionné est invalide.',
            'personel_id.uuid' => 'L\'identifiant du personnel doit être un UUID valide.',
            'personel_id.exists' => 'Le personnel sélectionné est introuvable.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'date_fin.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
            'adresse_ip.string' => 'L\'adresse IP doit être une chaîne de caractères.',
            'adresse_ip.max' => 'L\'adresse IP ne doit pas dépasser 45 caractères.',
            'search.string' => 'Le terme de recherche doit être une chaîne de caractères.',
            'per_page.integer' => 'Le paramètre per_page doit être un entier.',
            'per_page.min' => 'Le paramètre per_page doit être au moins de 1.',
            'per_page.max' => 'Le paramètre per_page ne doit pas dépasser 200.',
            'sort_by.in' => 'Le tri n\'est autorisé que sur la date de création.',
            'sort_dir.in' => 'La direction de tri doit être asc ou desc.',
        ];
    }
}
