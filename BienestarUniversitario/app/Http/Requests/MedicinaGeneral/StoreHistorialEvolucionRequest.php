<?php

namespace App\Http\Requests\MedicinaGeneral;

use Illuminate\Foundation\Http\FormRequest;

class StoreHistorialEvolucionRequest extends FormRequest
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
            'detalle_evolucion' => ['required', 'string'],
            'prescripcion_medica' => ['nullable', 'string'],
        ];
    }
}
