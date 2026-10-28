<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreCareerStudyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 1 = Médico es solo para el personal; un paciente no puede asignárselo
            'id_tipo_usuario' => ['required', 'integer', 'in:2,3,4,5'],
            'id_facultad' => ['required_if:id_tipo_usuario,2', 'nullable', 'exists:facultad,id'],
            'id_carrera' => ['required_if:id_tipo_usuario,2', 'nullable', 'exists:carrera,id'],
            'id_ciclo' => ['required_if:id_tipo_usuario,2', 'nullable', 'exists:ciclos,id'],
        ];
    }
}
