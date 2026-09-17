<?php

namespace App\Http\Requests\Enfermeria;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcedimientoEnfermeriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_procedimiento' => ['required', 'string', 'max:255'],
        ];
    }
}
