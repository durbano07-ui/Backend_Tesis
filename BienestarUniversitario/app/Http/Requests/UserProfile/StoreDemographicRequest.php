<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemographicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_identificacion_etnica' => ['nullable', 'exists:identificacion_etnica,id'],
            'id_genero' => ['nullable', 'exists:identificacion_genero,id'],
            'id_estado_civil' => ['nullable', 'exists:identificacion_estado_civil,id'],
            'nacionalidad' => ['nullable', 'string', 'max:100'],
        ];
    }
}
