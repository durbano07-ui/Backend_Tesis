<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\LugarTrabajo;
use Illuminate\Database\Seeder;

class CampusFacultadesSeeder extends Seeder
{
    /**
     * Run the database seeds for Campus, Facultades y Carreras.
     */
    public function run(): void
    {
        $this->command?->info('Sembrando Campus (Lugares de Trabajo)...');

        // 1. Catálogo de Campus (Lugar de Trabajo)
        $campusList = [
            'Campus Guanujo',
            'Campus Laguacoto',
            'Campus San Miguel',
            'Campus Chimbo',
        ];

        foreach ($campusList as $campusNombre) {
            LugarTrabajo::firstOrCreate(['nombre' => $campusNombre]);
        }

        $this->command?->info('Sembrando Facultades y Carreras...');

        // 2. Facultades y sus respectivas Carreras
        $facultadesConCarreras = [
            'Facultad de Ciencias Administrativas, Gestión Empresarial e Informática' => [
                'Software',
                'Tecnologías de la Información',
                'Administración de Empresas',
                'Contabilidad y Auditoría',
                'Mercadotecnia',
                'Marketing Digital',
                'Comunicación',
                'Turismo y Hotelería',
                'Emprendimiento e Innovación Social',
            ],
            'Facultad de Jurisprudencia, Ciencias Sociales y Políticas' => [
                'Derecho',
                'Sociología',
            ],
            'Facultad de Ciencias de la Educación, Sociales, Filosóficas y Humanísticas' => [
                'Educación Básica',
                'Educación Inicial',
                'Educación Intercultural Bilingüe',
                'Pedagogía de los Idiomas Nacionales y Extranjeros',
                'Pedagogía de la Matemática y la Física',
                'Pedagogía de las Ciencias Experimentales - Informática',
            ],
            'Facultad de Ciencias Agropecuarias, Recursos Naturales y del Ambiente' => [
                'Agroindustrias',
                'Agronomía',
                'Medicina Veterinaria',
            ],
            'Facultad de Ciencias de la Salud y del Ser Humano' => [
                'Enfermería',
                'Terapia Física',
                'Psicología',
                'Ingeniería en Riesgos de Desastres',
            ],
            'Extensión Universitaria de San Miguel' => [
                'Criminalística',
                'Gestión del Talento Humano',
            ],
        ];

        foreach ($facultadesConCarreras as $facultadNombre => $carreras) {
            $facultad = Facultad::firstOrCreate(['nombre' => $facultadNombre]);

            foreach ($carreras as $carreraNombre) {
                Carrera::firstOrCreate([
                    'id_facultad' => $facultad->id,
                    'nombre' => $carreraNombre,
                ]);
            }
        }

        $this->command?->info('✓ Campus, Facultades y Carreras cargados exitosamente.');
    }
}
