<?php

namespace App\Http\Requests\Enfermeria;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParteDiarioEnfermeriaRequest extends FormRequest
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
            'tipo_atencion' => ['sometimes', 'in:primaria,secundaria'],
            'tipo' => ['sometimes', 'in:curativo,preventivo'],
            'detalle_procedimiento' => ['nullable', 'string'],
            'detalle_medicacion' => ['nullable', 'string'],
            'id_procedimiento_enfermeria' => ['nullable', 'integer', 'exists:procedimientos_enfermeria,id'],
        ];
    }
}
