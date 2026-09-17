<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmergencyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'max:255'],
            'parentesco' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
        ];
    }
}
