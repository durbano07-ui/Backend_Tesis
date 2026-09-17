<?php

namespace App\Http\Requests\Odontologia;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogoInsumosOdontologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
        ];
    }
}
