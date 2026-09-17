<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'paciente',
            'enfermero',
            'medico_general',
            'psicologo',
            'odontologo',
            'medico_ocupacional',
            'medico_coordinador',
            'administrador',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'sanctum']
            );
        }
    }
}
