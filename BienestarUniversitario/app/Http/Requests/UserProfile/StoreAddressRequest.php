<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'id_provincia' => ['nullable', 'exists:provincia,id'],
            'id_canton' => ['nullable', 'exists:provincia_canton,id'],
            'direccion_referencia' => ['nullable', 'string', 'max:255'],
            'telefono_convencional' => ['nullable', 'string', 'max:20'],
            'correo_electronico' => ['nullable', 'email', 'max:255'],
            'nacionalidad' => ['nullable', 'string', 'max:100'],
            'id_tipo_direccion' => ['required', 'exists:tipo_direccion,id'],
            'es_extranjero' => ['boolean'],
        ];

        if ($this->boolean('es_extranjero')) {
            $rules['id_provincia'] = ['nullable'];
            $rules['id_canton'] = ['nullable'];
        }

        return $rules;
    }
}
