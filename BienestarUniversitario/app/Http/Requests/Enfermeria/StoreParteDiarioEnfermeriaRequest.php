<?php

namespace App\Http\Requests\Enfermeria;

use Illuminate\Foundation\Http\FormRequest;

class StoreParteDiarioEnfermeriaRequest extends FormRequest
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
            'tipo_atencion' => ['required', 'in:primaria,secundaria'],
            'tipo' => ['required', 'in:curativo,preventivo'],
            'detalle_procedimiento' => ['nullable', 'string'],
            'detalle_medicacion' => ['nullable', 'string'],
            'id_procedimiento_enfermeria' => ['nullable', 'integer', 'exists:procedimientos_enfermeria,id'],
        ];
    }
}
