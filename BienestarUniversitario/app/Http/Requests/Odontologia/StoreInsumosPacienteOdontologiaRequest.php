<?php

namespace App\Http\Requests\Odontologia;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsumosPacienteOdontologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario_paciente' => 'required|exists:users,id',
            'id_insumo' => 'required|exists:catalogo_insumos_odontologia,id',
            'cantidad_gastada' => 'required|string|max:50',
        ];
    }
}
