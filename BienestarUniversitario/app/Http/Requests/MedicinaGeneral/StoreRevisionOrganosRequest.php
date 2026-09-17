<?php

namespace App\Http\Requests\MedicinaGeneral;

use Illuminate\Foundation\Http\FormRequest;

class StoreRevisionOrganosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => ['required', 'integer', 'exists:users,id'],
            'detalle_revision_organos' => ['required', 'string'],
            'posicion_x' => ['nullable', 'integer'],
            'posicion_y' => ['nullable', 'integer'],
        ];
    }
}
