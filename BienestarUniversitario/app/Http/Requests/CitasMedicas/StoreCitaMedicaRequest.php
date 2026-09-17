<?php

namespace App\Http\Requests\CitasMedicas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCitaMedicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_doctor' => ['required', 'integer', 'exists:users,id'],
            'rol_doctor' => ['required', 'string', Rule::in(['medico_general', 'psicologo', 'odontologo', 'medico_ocupacional'])],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'string', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rol_doctor.in' => 'El rol del doctor debe ser: medico_general, psicologo, odontologo o medico_ocupacional',
            'fecha.after_or_equal' => 'La fecha de la cita debe ser hoy o en el futuro',
        ];
    }
}