<?php

namespace App\Http\Requests\Odontologia;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatalogoInsumosOdontologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|required|string|max:255',
            'cantidad_llegada' => 'nullable|integer|min:1',
            'stock' => 'nullable|integer|min:0',
        ];
    }
}