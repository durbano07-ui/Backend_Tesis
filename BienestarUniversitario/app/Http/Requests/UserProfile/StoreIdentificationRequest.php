<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIdentificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'apellido_paterno' => ['required', 'string', 'max:100', 'regex:/^[\pL\pM\s\'.\-]+$/u'],
            'apellido_materno' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pM\s\'.\-]+$/u'],
            'primer_nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\pM\s\'.\-]+$/u'],
            'segundo_nombre' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pM\s\'.\-]+$/u'],
            'numero_cedula' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('datos_identificacion', 'numero_cedula')->ignore($this->user()->id, 'id_usuario'),
            ],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
        ];
    }
}
