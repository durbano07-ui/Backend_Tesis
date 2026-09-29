<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['medico_coordinador', 'administrador']);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('roles') && is_array($this->roles)) {
            $roleMap = [
                'estudiante' => 'paciente',
                'docente' => 'paciente',
                'administrativo' => 'paciente',
                'codigo_trabajo' => 'paciente',
            ];
            $normalizedRoles = array_map(function ($role) use ($roleMap) {
                return $roleMap[$role] ?? $role;
            }, $this->roles);

            $this->merge([
                'roles' => array_values(array_unique($normalizedRoles)),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                'regex:/@ueb\.edu\.ec$/i',
            ],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,name'],
            'id_tipo_usuario' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.regex' => 'Only institutional emails (@ueb.edu.ec) are allowed',
            'email.unique' => 'Email already registered',
            'roles.*.exists' => 'Invalid role for this action',
        ];
    }
}
