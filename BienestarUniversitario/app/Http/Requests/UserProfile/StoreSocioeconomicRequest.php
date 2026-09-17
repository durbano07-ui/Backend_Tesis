<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocioeconomicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nivel_instruccion_jefe_hogar' => ['nullable', 'string', 'max:100'],
            'empleo_jefe_hogar' => ['nullable', 'string', 'max:100'],
            'ingresos_mensuales' => ['nullable', 'string', 'max:100'],
            'tipo_vivienda' => ['nullable', 'string', 'max:100'],
            'numero_personas_hogar' => ['nullable', 'integer', 'min:1'],
            'numero_aportantes' => ['nullable', 'integer', 'min:1'],
            'posee_internet' => ['required', 'boolean'],
            'posee_computadora' => ['required', 'boolean'],
            'recibe_beca' => ['required', 'boolean'],
        ];
    }
}
