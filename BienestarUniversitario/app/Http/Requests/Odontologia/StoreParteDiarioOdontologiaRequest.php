<?php

namespace App\Http\Requests\Odontologia;

use Illuminate\Foundation\Http\FormRequest;

class StoreParteDiarioOdontologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'tipo_atencion' => 'required|in:primaria,secundaria,certificadomedico,validacion',
            'tipo_atencion2' => 'required|in:curativo,preventivo',
            'detalle_diagnostico' => 'nullable|string',
            'procedimiento' => 'nullable|string|max:255',
            'prescripcion_lineas' => 'nullable|array',
        ];
    }
}
