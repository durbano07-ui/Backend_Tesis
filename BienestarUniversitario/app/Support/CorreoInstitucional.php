<?php

namespace App\Support;

/**
 * Dominios de correo institucional de la UEB aceptados al registrar usuarios
 * (deben coincidir con src/utils/correoInstitucional.js del frontend).
 */
final class CorreoInstitucional
{
    public const DOMINIOS = ['ueb.edu.ec', 'mailes.ueb.edu.ec'];

    /** Regla de validación: el correo termina en uno de los dominios institucionales. */
    public const REGLA = 'regex:/@(mailes\.)?ueb\.edu\.ec$/i';

    public static function texto(string $separador = ' o '): string
    {
        return implode($separador, array_map(fn ($d) => "@{$d}", self::DOMINIOS));
    }
}
