<?php

namespace App\Support;

use App\Models\DireccionUsuario;
use App\Models\User;

/**
 * Cédula de identidad ecuatoriana (persona natural), validada con el algoritmo del Registro Civil:
 *  - 10 dígitos; los dos primeros son la provincia (01–24, o 30 para ecuatorianos en el exterior);
 *  - el tercero es menor que 6;
 *  - el décimo es el dígito verificador (módulo 10 con coeficientes 2,1,2,1,2,1,2,1,2).
 */
final class CedulaEcuatoriana
{
    public static function esValida(?string $cedula): bool
    {
        if ($cedula === null || !preg_match('/^\d{10}$/', $cedula)) {
            return false;
        }

        $provincia = (int) substr($cedula, 0, 2);
        if (!(($provincia >= 1 && $provincia <= 24) || $provincia === 30)) {
            return false;
        }

        if ((int) $cedula[2] >= 6) {
            return false;
        }

        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $valor = (int) $cedula[$i] * ($i % 2 === 0 ? 2 : 1);
            $suma += $valor > 9 ? $valor - 9 : $valor;
        }
        $verificador = (10 - $suma % 10) % 10;

        return $verificador === (int) $cedula[9];
    }

    /** Pasaporte (extranjeros): alfanumérico de 5 a 20 caracteres. */
    public static function esPasaporteValido(?string $documento): bool
    {
        return $documento !== null && (bool) preg_match('/^[A-Za-z0-9-]{5,20}$/', $documento);
    }

    /**
     * Cuentas existentes que deben corregir su cédula: tienen un documento numérico que no pasa el
     * algoritmo y no están registradas como extranjeras (un documento con letras es un pasaporte).
     */
    public static function requiereCorreccion(User $user): bool
    {
        $cedula = $user->datosIdentificacion?->numero_cedula;
        if ($cedula === null || $cedula === '' || !ctype_digit($cedula)) {
            return false;
        }
        if (DireccionUsuario::where('id_usuario', $user->id)->where('es_extranjero', true)->exists()) {
            return false;
        }

        return !self::esValida($cedula);
    }
}
