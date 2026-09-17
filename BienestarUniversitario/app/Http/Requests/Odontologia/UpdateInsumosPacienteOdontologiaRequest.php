<?php

namespace App\Http\Requests\Odontologia;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInsumosPacienteOdontologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => 'sometimes|required|exists:users,id',
            'id_insumo' => 'sometimes|required|exists:catalogo_insumos_odontologia,id',
            'cantidad_gastada' => 'sometimes|required|string|max:50',
        ];
    }
}
