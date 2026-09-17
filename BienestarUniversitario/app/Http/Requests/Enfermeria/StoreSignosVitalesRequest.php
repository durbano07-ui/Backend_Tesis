<?php

namespace App\Http\Requests\Enfermeria;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignosVitalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => ['required', 'integer', 'exists:users,id'],
            'id_usuario_medico_general' => ['nullable', 'integer', 'exists:users,id'],
            'fecha' => ['required', 'date'],
            'presion_arterial_diastolica' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'presion_arterial_sistolica' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0', 'max:300'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0', 'max:100'],
            'temperatura' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'talla' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'peso' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        ];
    }
}