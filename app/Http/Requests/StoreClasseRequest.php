<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClasseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nom' => [
                'required',
                'string',
                'max:255',
                'unique:classes,nom,' . ($this->classe ? $this->classe->id : 'NULL')
            ],
            'exam' => 'nullable|string|max:255',
            'annee_scolaire' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{4}$/'
            ]
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le nom de la classe est obligatoire',
            'nom.unique' => 'Une classe avec ce nom existe déjà',
            'annee_scolaire.required' => 'L\'année scolaire est obligatoire',
            'annee_scolaire.regex' => 'Format invalide. Utilisez AAAA-AAAA (ex: 2024-2025)',
        ];
    }
}
