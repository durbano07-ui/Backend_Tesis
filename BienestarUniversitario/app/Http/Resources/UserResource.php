<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $identificacion = $this->datosIdentificacion;
        $nombreCompleto = null;

        if ($identificacion) {
            $nombreCompleto = trim(implode(' ', array_filter([
                $identificacion->primer_nombre,
                $identificacion->segundo_nombre,
                $identificacion->apellido_paterno,
                $identificacion->apellido_materno,
            ])));
        }

        if (empty($nombreCompleto)) {
            $nombreCompleto = $this->name;
        }

        if (empty($nombreCompleto) && $this->email) {
            $handle = explode('@', $this->email)[0];
            $nombreCompleto = ucwords(str_replace(['.', '_', '-'], ' ', $handle));
        }

        $cedula = $identificacion?->numero_cedula;
        if (empty($cedula)) {
            $cedula = '020' . str_pad($this->id, 7, '0', STR_PAD_LEFT);
        }

        return [
            'id' => $this->id,
            'name' => $this->name ?: $nombreCompleto,
            'nombre_completo' => $nombreCompleto,
            'numero_cedula' => $cedula,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'id_tipo_usuario' => $this->hasAnyRole(['enfermero', 'medico_general', 'psicologo', 'odontologo', 'medico_ocupacional', 'medico_coordinador', 'administrador']) ? 1 : ($this->estudioCarrera?->id_tipo_usuario ?? 2),
            'activo' => $this->activo,
            'must_change_password' => $this->must_change_password,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
