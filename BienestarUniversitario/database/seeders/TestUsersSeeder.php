<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Paciente
        $paciente = \App\Models\User::updateOrCreate(
            ['email' => 'paciente@ueb.edu.ec'],
            [
                'name' => 'Paciente Demo',
                'password' => Hash::make('password123'),
                'activo' => true,
                'must_change_password' => false,
            ]
        );
        $pacienteRole = Role::where('name', 'paciente')->first();
        if ($pacienteRole) {
            $paciente->syncRoles([$pacienteRole]);
        }
        \App\Models\DatosIdentificacion::updateOrCreate(
            ['id_usuario' => $paciente->id],
            [
                'primer_nombre' => 'Juan',
                'segundo_nombre' => 'Carlos',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'García',
                'numero_cedula' => '1712345678',
                'fecha_nacimiento' => '1995-05-15',
            ]
        );

        // 2. Enfermero
        $enfermero = \App\Models\User::updateOrCreate(
            ['email' => 'enfermero@ueb.edu.ec'],
            [
                'name' => 'Enfermero Demo',
                'password' => Hash::make('password123'),
                'activo' => true,
                'must_change_password' => false,
            ]
        );
        $enfermeroRole = Role::where('name', 'enfermero')->first();
        if ($enfermeroRole) {
            $enfermero->syncRoles([$enfermeroRole]);
        }
        \App\Models\DatosIdentificacion::updateOrCreate(
            ['id_usuario' => $enfermero->id],
            [
                'primer_nombre' => 'Ana',
                'segundo_nombre' => 'María',
                'apellido_paterno' => 'Mendoza',
                'apellido_materno' => 'Castro',
                'numero_cedula' => '0201234567',
                'fecha_nacimiento' => '1988-10-22',
            ]
        );

        // 3. Médico General
        $medicoGeneral = \App\Models\User::updateOrCreate(
            ['email' => 'medico.general@ueb.edu.ec'],
            [
                'name' => 'Médico General Demo',
                'password' => Hash::make('password123'),
                'activo' => true,
                'must_change_password' => false,
            ]
        );
        $medicoGeneralRole = Role::where('name', 'medico_general')->first();
        if ($medicoGeneralRole) {
            $medicoGeneral->syncRoles([$medicoGeneralRole]);
        }
        \App\Models\DatosIdentificacion::updateOrCreate(
            ['id_usuario' => $medicoGeneral->id],
            [
                'primer_nombre' => 'Carlos',
                'segundo_nombre' => 'Andrés',
                'apellido_paterno' => 'Díaz',
                'apellido_materno' => 'Torres',
                'numero_cedula' => '0207654321',
                'fecha_nacimiento' => '1980-04-12',
            ]
        );

        // 4. Médico Ocupacional
        $medicoOcupacional = \App\Models\User::updateOrCreate(
            ['email' => 'medico.ocupacional@ueb.edu.ec'],
            [
                'name' => 'Médico Ocupacional Demo',
                'password' => Hash::make('password123'),
                'activo' => true,
                'must_change_password' => false,
            ]
        );
        $medicoOcupacionalRole = Role::where('name', 'medico_ocupacional')->first();
        if ($medicoOcupacionalRole) {
            $medicoOcupacional->syncRoles([$medicoOcupacionalRole]);
        }
        \App\Models\DatosIdentificacion::updateOrCreate(
            ['id_usuario' => $medicoOcupacional->id],
            [
                'primer_nombre' => 'Luis',
                'segundo_nombre' => 'Alfonso',
                'apellido_paterno' => 'Salazar',
                'apellido_materno' => 'Ramos',
                'numero_cedula' => '0209876543',
                'fecha_nacimiento' => '1975-08-30',
            ]
        );

        // 5. Psicólogo
        $psicologo = \App\Models\User::updateOrCreate(
            ['email' => 'psicologo@ueb.edu.ec'],
            [
                'name' => 'Psicólogo Demo',
                'password' => Hash::make('password123'),
                'activo' => true,
                'must_change_password' => false,
            ]
        );
        $psicologoRole = Role::where('name', 'psicologo')->first();
        if ($psicologoRole) {
            $psicologo->syncRoles([$psicologoRole]);
        }
        \App\Models\DatosIdentificacion::updateOrCreate(
            ['id_usuario' => $psicologo->id],
            [
                'primer_nombre' => 'Gabriela',
                'segundo_nombre' => 'Lucía',
                'apellido_paterno' => 'Vargas',
                'apellido_materno' => 'Morales',
                'numero_cedula' => '0204561239',
                'fecha_nacimiento' => '1985-12-05',
            ]
        );

        // 6. Odontólogo
        $odontologo = \App\Models\User::updateOrCreate(
            ['email' => 'odontologo@ueb.edu.ec'],
            [
                'name' => 'Odontólogo Demo',
                'password' => Hash::make('password123'),
                'activo' => true,
                'must_change_password' => false,
            ]
        );
        $odontologoRole = Role::where('name', 'odontologo')->first();
        if ($odontologoRole) {
            $odontologo->syncRoles([$odontologoRole]);
        }
        \App\Models\DatosIdentificacion::updateOrCreate(
            ['id_usuario' => $odontologo->id],
            [
                'primer_nombre' => 'Roberto',
                'segundo_nombre' => 'Esteban',
                'apellido_paterno' => 'Silva',
                'apellido_materno' => 'Paz',
                'numero_cedula' => '0207894561',
                'fecha_nacimiento' => '1982-03-18',
            ]
        );

        // 7. Médico Coordinador
        $coordinador = \App\Models\User::updateOrCreate(
            ['email' => 'medico.coordinador@ueb.edu.ec'],
            [
                'name' => 'Médico Coordinador Demo',
                'password' => Hash::make('password123'),
                'activo' => true,
                'must_change_password' => false,
            ]
        );
        $coordinadorRole = Role::where('name', 'medico_coordinador')->first();
        if ($coordinadorRole) {
            $coordinador->syncRoles([$coordinadorRole]);
        }
        \App\Models\DatosIdentificacion::updateOrCreate(
            ['id_usuario' => $coordinador->id],
            [
                'primer_nombre' => 'Patricia',
                'segundo_nombre' => 'Beatriz',
                'apellido_paterno' => 'Gómez',
                'apellido_materno' => 'Rojas',
                'numero_cedula' => '0206549873',
                'fecha_nacimiento' => '1979-06-25',
            ]
        );

        // 8. Administrador
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@ueb.edu.ec'],
            [
                'name' => 'Administrador Demo',
                'password' => Hash::make('password123'),
                'activo' => true,
                'must_change_password' => false,
            ]
        );
        $adminRole = Role::where('name', 'administrador')->first();
        if ($adminRole) {
            $admin->syncRoles([$adminRole]);
        }
        \App\Models\DatosIdentificacion::updateOrCreate(
            ['id_usuario' => $admin->id],
            [
                'primer_nombre' => 'Admin',
                'segundo_nombre' => 'Sistema',
                'apellido_paterno' => 'Bienestar',
                'apellido_materno' => 'Universitario',
                'numero_cedula' => '0200000001',
                'fecha_nacimiento' => '1990-01-01',
            ]
        );

        $this->command->info('Demo users for @ueb.edu.ec seeded successfully:');
        $this->command->info('  - Paciente: paciente / password123');
        $this->command->info('  - Enfermero: enfermero / password123');
        $this->command->info('  - Médico General: medico.general / password123');
        $this->command->info('  - Médico Ocupacional: medico.ocupacional / password123');
        $this->command->info('  - Psicólogo: psicologo / password123');
        $this->command->info('  - Odontólogo: odontologo / password123');
        $this->command->info('  - Coordinador: medico.coordinador / password123');
        $this->command->info('  - Administrador: admin / password123');
    }
}
