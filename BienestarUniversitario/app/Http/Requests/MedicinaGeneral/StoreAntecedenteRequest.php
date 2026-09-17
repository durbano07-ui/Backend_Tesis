<?php

namespace App\Http\Requests\MedicinaGeneral;

use Illuminate\Foundation\Http\FormRequest;

class StoreAntecedenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => ['required', 'integer', 'exists:users,id'],
            'tipo' => ['required', 'in:personal,familiar'],
            'detalle_antecedente' => ['nullable', 'string'],
        ];
    }
}
