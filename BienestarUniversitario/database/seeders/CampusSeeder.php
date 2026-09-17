<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactivar temporalmente restricciones de clave foránea
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // Borrar asignaciones existentes de campus para forzar que vuelva a preguntar
        DB::table('usuario_lugar_de_trabajo')->truncate();

        // Limpiar catálogo de campus
        DB::table('lugar_trabajo')->truncate();

        // Insertar exactamente los dos campus requeridos
        DB::table('lugar_trabajo')->insert([
            ['nombre' => 'Campus Guanujo'],
            ['nombre' => 'Campus Laguacoto']
        ]);

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }
}
