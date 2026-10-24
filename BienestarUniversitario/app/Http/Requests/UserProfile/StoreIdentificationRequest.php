<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CedulaEcuatorianaValida;
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
            'es_extranjero' => ['sometimes', 'boolean'],
            // Ecuatorianos: cédula validada con el algoritmo del Registro Civil; extranjeros: pasaporte
            'numero_cedula' => [
                'nullable',
                'string',
                'max:20',
                $this->boolean('es_extranjero') ? 'regex:/^[A-Za-z0-9-]{5,20}$/' : new CedulaEcuatorianaValida(),
                Rule::unique('datos_identificacion', 'numero_cedula')->ignore($this->user()->id, 'id_usuario'),
            ],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
        ];
    }
}
