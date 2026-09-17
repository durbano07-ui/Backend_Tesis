<?php

namespace App\Http\Requests\Enfermeria;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSignosVitalesRequest extends FormRequest
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
