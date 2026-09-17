<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;

class AsignarPacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => ['required', 'exists:users,id'],
            'id_cargo' => ['required', 'exists:listado_cargo_personal_medicoocupacional,id'],
        ];
    }
}
