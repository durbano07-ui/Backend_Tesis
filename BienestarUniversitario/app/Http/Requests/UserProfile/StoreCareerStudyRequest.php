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
            'id_tipo_usuario' => ['required', 'exists:tipo_usuario,id'],
            'id_facultad' => ['required_if:id_tipo_usuario,2', 'nullable', 'exists:facultad,id'],
            'id_carrera' => ['required_if:id_tipo_usuario,2', 'nullable', 'exists:carrera,id'],
            'id_ciclo' => ['required_if:id_tipo_usuario,2', 'nullable', 'exists:ciclos,id'],
        ];
    }
}
