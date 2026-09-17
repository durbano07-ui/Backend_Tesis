<?php

namespace App\Http\Requests\Odontologia;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParteDiarioOdontologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => 'sometimes|required|exists:users,id',
            'fecha' => 'sometimes|required|date',
            'tipo_atencion' => 'sometimes|required|in:primaria,secundaria,certificadomedico,validacion',
            'tipo_atencion2' => 'sometimes|required|in:curativo,preventivo',
            'detalle_diagnostico' => 'nullable|string',
            'procedimiento' => 'sometimes|nullable|string|max:255',
            'prescripcion_lineas' => 'nullable|array',
        ];
    }
}
