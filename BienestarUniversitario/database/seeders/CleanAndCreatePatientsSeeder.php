<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DatosIdentificacion;
use App\Models\UsuarioEstudiaCarrera;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CleanAndCreatePatientsSeeder extends Seeder
{
    public function run(): void
    {
        $staffRoles = ['administrador', 'medico_coordinador', 'enfermero', 'medico_general', 'medico_ocupacional', 'psicologo', 'odontologo'];

        // 1. Delete all existing patient users (everyone who is not a staff member)
        // We do this first while foreign key constraints are enabled to allow proper cascade deletes!
        $this->command->info('Cleaning up non-staff patient users from users table (with active foreign keys)...');
        $users = User::all();
        
        foreach ($users as $user) {
            if ($user->hasAnyRole($staffRoles)) {
                continue;
            }
            $this->command->info("Deleting patient user: {$user->email} (ID: {$user->id})");
            $user->delete(); // Cascades deletes to related identification/address records!
        }

        // Clean up any patient-like emails or dangling user records
        $danglingEmails = ['paciente@ueb.edu.ec', 'estudiante@ueb.edu.ec', 'docente@ueb.edu.ec', 'administrativo@ueb.edu.ec', 'paciente@test.com'];
        $danglingUsers = User::whereIn('email', $danglingEmails)->get();
        foreach ($danglingUsers as $du) {
            $this->command->info("Deleting dangling user: {$du->email} (ID: {$du->id})");
            $du->delete();
        }

        // 2. Explicitly wipe out identification and academic records that do not belong to staff
        $this->command->info('Cleaning up orphaned patient profiles in datos_identificacion and usuario_estudia_carrera...');
        
        // Get all staff user IDs
        $staffIds = User::all()->filter(function($u) use ($staffRoles) {
            return $u->hasAnyRole($staffRoles);
        })->pluck('id')->toArray();

        // Delete any identification and study career records not belonging to staff
        DatosIdentificacion::whereNotIn('id_usuario', $staffIds)->delete();
        UsuarioEstudiaCarrera::whereNotIn('id_usuario', $staffIds)->delete();

        // 3. Disable constraints to truncate clinical tables safely
        $this->command->info('Disabling foreign key constraints...');
        Schema::disableForeignKeyConstraints();

        // Truncate all clinical, medical, and patient demographic/address data
        $clinicalTables = [
            'antecedentes_medicina',
            'diagnosticos_medicina',
            'enfermedades_actuales_medicina',
            'examen_fisico_medicina',
            'motivo_consulta_medicina',
            'parte_diario_enfermeria',
            'parte_diario_medicina',
            'parte_diario_psicologia',
            'parte_diario_odontologia',
            'planes_terapeuticos_medicina',
            'revision_organos_medicina',
            'signos_vitales',
            'usuario_tiene_cargo',
            'ficha_socioeconomica',
            'insumos_paciente_odontologia',
            'odontograma_asignacion_odontologia',
            'odontograma_paciente_odontologia',
            'analisis_resultados_psicologia',
            'conclusiones_psicologia',
            'diagnostico_psicologia',
            'examen_estado_mental_psicologia',
            'historial_evolucion_medicina',
            'historial_evolucion_psicologia',
            'historial_evolucion_odontologia',
            'historial_laboral_psicologia',
            'historial_sexual_psicologia',
            'historial_social_psicologia',
            'motivo_consulta_psicologia',
            'motivo_consulta_odontologia',
            'examen_odontologia',
            'enfermedad_periodontal_odontologia',
            'patologias_psicologia',
            'pronostico_psicologia',
            'pruebas_aplicadas_psicologia',
            'psicoanamnesis_psicologia',
            'recomendacion_psicologia',
            'accidentes_laborales_medico',
            'ausentismo_laboral_medicoocupacional',
            'cese_de_funciones_medicoocupacional',
            'enfermedades_catastroficas_medico',
            'enfermedades_nuevas_medico',
            'funcionarios_discapacidad_medico',
            'grupo_riesgo_psico_medico',
            'grupo_vulnerable_medico',
            'historial_vacunas_medicoocupacional',
            'linea_receta_medicoocupacional',
            'orden_de_examen_medicoocupacional',
            'orden_de_examen_otros_medicoocupacional',
            'orden_de_examen_tipo_medicoocupacional',
            'personal_nuevo_medicoocupacional',
            'receta_cie_medicoocupacional',
            'receta_medicoocupacional',
            'receta_recomendaciones_nofarmaco_medicoocupacional',
            'reintegro_ueb_medicoocupacional',
            'signo_alarma_receta_medicoocupacional',
            'sospechosos_confirmados_influenza',
            'usuario_tiene_alergias',
            'usuario_tiene_discapacidad',
            'usuario_tiene_hijos',
            'direcciones_usuario',
            'contactos_emergencias',
            'datos_autopercepcion_ciudadana',
            'listado_embarazadas_medico',
            'listado_discapacidades_medico'
        ];

        $this->command->info('Truncating clinical and patient-related tables...');
        foreach ($clinicalTables as $table) {
            DB::table($table)->truncate();
        }

        // 4. Re-enable foreign key constraints
        $this->command->info('Re-enabling foreign key constraints...');
        Schema::enableForeignKeyConstraints();

        $this->command->info('Database fully cleaned. Creating 3 fresh, empty patient profiles...');

        $pacienteRole = Role::where('name', 'paciente')->first();
        if (!$pacienteRole) {
            $this->command->error('Role "paciente" not found!');
            return;
        }

        // 5. Create Student Patient (Estudiante)
        $student = User::create([
            'name' => 'Estudiante Demo',
            'email' => 'estudiante@ueb.edu.ec',
            'password' => Hash::make('password123'),
            'activo' => true,
            'must_change_password' => false,
        ]);
        $student->assignRole($pacienteRole);
        
        DatosIdentificacion::create([
            'id_usuario' => $student->id,
            'primer_nombre' => 'Estudiante',
            'segundo_nombre' => 'Demo',
            'apellido_paterno' => 'Bienestar',
            'apellido_materno' => 'Universitario',
            'numero_cedula' => '0201111111',
            'fecha_nacimiento' => '2002-05-10',
        ]);

        UsuarioEstudiaCarrera::create([
            'id_usuario' => $student->id,
            'id_facultad' => 1,
            'id_carrera' => 1,
            'id_ciclo' => 3,
            'id_tipo_usuario' => 2, // Estudiante
        ]);
        
        $this->command->info('  - Student created: estudiante@ueb.edu.ec / password123');

        // 6. Create Administrative Patient (Administrativo)
        $adminPatient = User::create([
            'name' => 'Administrativo Demo',
            'email' => 'administrativo@ueb.edu.ec',
            'password' => Hash::make('password123'),
            'activo' => true,
            'must_change_password' => false,
        ]);
        $adminPatient->assignRole($pacienteRole);
        
        DatosIdentificacion::create([
            'id_usuario' => $adminPatient->id,
            'primer_nombre' => 'Administrativo',
            'segundo_nombre' => 'Demo',
            'apellido_paterno' => 'Bienestar',
            'apellido_materno' => 'Universitario',
            'numero_cedula' => '0202222222',
            'fecha_nacimiento' => '1985-08-15',
        ]);

        UsuarioEstudiaCarrera::create([
            'id_usuario' => $adminPatient->id,
            'id_tipo_usuario' => 4, // Administrativo
        ]);

        $this->command->info('  - Administrative created: administrativo@ueb.edu.ec / password123');

        // 7. Create Teacher Patient (Docente)
        $teacher = User::create([
            'name' => 'Docente Demo',
            'email' => 'docente@ueb.edu.ec',
            'password' => Hash::make('password123'),
            'activo' => true,
            'must_change_password' => false,
        ]);
        $teacher->assignRole($pacienteRole);
        
        DatosIdentificacion::create([
            'id_usuario' => $teacher->id,
            'primer_nombre' => 'Docente',
            'segundo_nombre' => 'Demo',
            'apellido_paterno' => 'Bienestar',
            'apellido_materno' => 'Universitario',
            'numero_cedula' => '0203333333',
            'fecha_nacimiento' => '1978-12-05',
        ]);

        UsuarioEstudiaCarrera::create([
            'id_usuario' => $teacher->id,
            'id_tipo_usuario' => 3, // Docente
        ]);

        $this->command->info('  - Docente created: docente@ueb.edu.ec / password123');
        $this->command->info('CleanAndCreatePatientsSeeder run successfully with full clinical history purge!');
    }
}
