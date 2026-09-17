<?php

namespace App\Http\Requests\MedicinaGeneral;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParteDiarioMedicinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => ['sometimes', 'integer', 'exists:users,id'],
            'fecha' => ['sometimes', 'date'],
            'tipo_atencion' => ['sometimes', 'in:primaria,secundaria,certificadomedico,validacion'],
            'tipo' => ['sometimes', 'in:curativo,preventivo'],
            'detalle_diagnostico' => ['nullable', 'string'],
        ];
    }
}
