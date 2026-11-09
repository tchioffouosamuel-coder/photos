<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportExcelRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Ajuster selon auth
    }
    
    public function rules()
    {
        return [
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240', // 10MB
            ],
            'classe_id' => [
                'required',
                'exists:classes,id'
            ]
        ];
    }
    
    public function messages()
    {
        return [
            'excel_file.required' => 'Veuillez sélectionner un fichier Excel',
            'excel_file.mimes' => 'Le fichier doit être au format Excel (.xlsx ou .xls)',
            'excel_file.max' => 'Le fichier ne doit pas dépasser 10MB',
            'classe_id.required' => 'Veuillez sélectionner une classe',
            'classe_id.exists' => 'Cette classe n\'existe pas',
        ];
    }
}