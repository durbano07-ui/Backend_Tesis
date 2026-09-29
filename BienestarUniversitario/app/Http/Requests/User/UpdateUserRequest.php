<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'id_tipo_usuario' => ['sometimes', 'nullable'],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
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
            'roles.*.exists' => 'El rol seleccionado no es válido',
        ];
    }
}
