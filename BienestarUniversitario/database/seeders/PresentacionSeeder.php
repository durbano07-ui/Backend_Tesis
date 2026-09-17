<?php

namespace Database\Seeders;

use App\Models\Presentacion;
use Illuminate\Database\Seeder;

class PresentacionSeeder extends Seeder
{
    public function run(): void
    {
        $presentaciones = [
            ['nombre' => 'Caja', 'descripcion' => 'Caja con múltiples unidades del medicamento'],
            ['nombre' => 'Blíster', 'descripcion' => 'Blíster con tabletas o cápsulas'],
            ['nombre' => 'Frasco', 'descripcion' => 'Frasco con tabletas, cápsulas o líquido'],
            ['nombre' => 'Tubo', 'descripcion' => 'Tubo con crema, pomada o gel'],
            ['nombre' => 'Ampolla', 'descripcion' => 'Ampolla con líquido inyectable'],
            ['nombre' => 'Vial', 'descripcion' => 'Vial con líquido inyectable'],
            ['nombre' => 'Sobre', 'descripcion' => 'Sobre con polvo para suspensión'],
            ['nombre' => 'Spray', 'descripcion' => 'Spray nasal o bucal'],
            ['nombre' => 'Gotas', 'descripcion' => 'Frasco con gotas'],
            ['nombre' => 'Inhalador', 'descripcion' => 'Inhalador para vía respiratoria'],
        ];

        foreach ($presentaciones as $presentacion) {
            Presentacion::firstOrCreate(
                ['nombre' => $presentacion['nombre']],
                ['descripcion' => $presentacion['descripcion']]
            );
        }
    }
}