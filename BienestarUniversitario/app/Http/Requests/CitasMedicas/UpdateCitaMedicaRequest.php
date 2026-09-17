<?php

namespace App\Http\Requests\CitasMedicas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCitaMedicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['sometimes', 'string', Rule::in(['programada', 'confirmada', 'completada', 'cancelada'])],
            'notas_doctor' => ['nullable', 'string', 'max:1000'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }
}