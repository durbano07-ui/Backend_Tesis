<?php

namespace App\Rules;

use App\Support\CedulaEcuatoriana;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CedulaEcuatorianaValida implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!CedulaEcuatoriana::esValida((string) $value)) {
            $fail('La cédula ingresada no es válida. Verifique los 10 dígitos.');
        }
    }
}
