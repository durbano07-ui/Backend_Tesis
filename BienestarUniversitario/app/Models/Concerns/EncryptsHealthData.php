<?php

namespace App\Models\Concerns;

/**
 * Cifrado en reposo de datos de salud (OWASP ASVS 4.0.3 — V6.1.2).
 *
 * Los campos listados en $encryptedHealthFields se guardan cifrados con AES-256-CBC + HMAC
 * (cast "encrypted" de Laravel, clave APP_KEY) y se descifran al leerlos desde el modelo.
 *
 * Importante:
 *  - Estos campos no se pueden filtrar ni ordenar en SQL (where/like/orderBy).
 *  - Si se pierde APP_KEY, los datos no se pueden recuperar. Para rotarla se usa
 *    APP_PREVIOUS_KEYS con la clave anterior.
 *  - Escribir con DB::table() o update() masivos se salta el cifrado: usar siempre el modelo.
 */
trait EncryptsHealthData
{
    public function initializeEncryptsHealthData(): void
    {
        $this->mergeCasts(array_fill_keys($this->encryptedHealthFields ?? [], 'encrypted'));
    }

    public static function encryptedHealthFieldList(): array
    {
        return (new static())->encryptedHealthFields ?? [];
    }
}
