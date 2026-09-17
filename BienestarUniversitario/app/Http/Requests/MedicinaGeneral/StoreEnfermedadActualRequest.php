<?php

namespace App\Http\Requests\MedicinaGeneral;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnfermedadActualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => ['required', 'integer', 'exists:users,id'],
            'detalle_enfermedad_actual' => ['required', 'string'],
        ];
    }
}
