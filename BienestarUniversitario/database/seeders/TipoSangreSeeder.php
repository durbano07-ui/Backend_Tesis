<?php

namespace Database\Seeders;

use App\Models\TipoSangre;
use Illuminate\Database\Seeder;

class TipoSangreSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        foreach ($tipos as $tipo) {
            TipoSangre::updateOrCreate(
                ['nombre' => $tipo],
                ['nombre' => $tipo]
            );
        }
    }
}