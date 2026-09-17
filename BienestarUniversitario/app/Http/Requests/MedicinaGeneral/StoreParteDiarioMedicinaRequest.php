<?php

namespace App\Http\Requests\MedicinaGeneral;

use Illuminate\Foundation\Http\FormRequest;

class StoreParteDiarioMedicinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => ['required', 'integer', 'exists:users,id'],
            'fecha' => ['required', 'date'],
            'tipo_atencion' => ['required', 'in:primaria,secundaria,certificadomedico,validacion'],
            'tipo' => ['required', 'in:curativo,preventivo'],
            'detalle_diagnostico' => ['nullable', 'string'],
        ];
    }
}
