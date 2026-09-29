<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\GrupoTipoExamenMedicoocupacional;
use App\Models\TipoExamenMedicoocupacional;

class ExamenesOcupacionalesLaboratorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = User::role('medico_ocupacional')->first()
            ?? User::role('administrador')->first()
            ?? User::first();

        $doctorId = $doctor ? $doctor->id : 1;

        $catalogo = [
            'HEMATOLOGÍA' => [
                'columna' => 1,
                'items' => [
                    'Hemograma Completo',
                    'Frotis en sangre periférica',
                    'Reticulocitos',
                    'Eritrosedimentación',
                    'Grupo Sanguíneo',
                    'R Coombs Directa',
                    'R Coombs Indirecta',
                    'Plasmodium (Gota Gruesa)',
                    'Plasmodium (Anticuerpos V y F)',
                ],
            ],
            'PERFIL DE ANEMIA' => [
                'columna' => 1,
                'items' => [
                    'Hierro Sérico',
                    'Capacidad de fijación de Hierro',
                    'Transferrina',
                    'Ferritina',
                    'Vitamina B12',
                    'Ácido Fólico',
                    'Eritropoyetina',
                    'Haptoglobina',
                    'Glucosa 6 Fosfato',
                    'Electroforesis de hemoglobina',
                    'Fragilidad Osmótica de hematíes',
                ],
            ],
            'PERFIL LIPÍDICO' => [
                'columna' => 1,
                'items' => [
                    'Aspecto del Suero',
                    'Colesterol',
                    'HDL Colesterol',
                    'LDL Colesterol',
                    'VLDL Colesterol',
                    'Triglicéridos',
                    'Lipoproteína A',
                    'Apolipoproteína A1',
                    'Apolipoproteína B',
                    'Índice Apo B/Apo A1',
                ],
            ],
            'ORINA' => [
                'columna' => 1,
                'items' => [
                    'Físico, Químico y Sedimento',
                    'Gram',
                    'Albúmina de Bence Jones',
                    'Cultivo',
                    'Cultivo para Hongos',
                    'Directo para B de K',
                    'Cultivo para B de K',
                    'Prueba de Embarazo',
                    'Cálculos (análisis)',
                    'Microalbuminuria',
                    'Cociente Microalbúmina/Creatinina',
                    'Pyridinix-D',
                    'Drogas: Marihuana',
                    'Nicotina',
                    'Cocaína',
                    'Tamizaje de Drogas (Panel 6)',
                    'Tamizaje de Drogas (Panel 10)',
                    'Sodio Urinario (Ocasional)',
                    'Potasio Urinario (Ocasional)',
                ],
            ],
            'BIOQUÍMICOS' => [
                'columna' => 2,
                'items' => [
                    'Urea',
                    'Bun',
                    'Creatinina',
                    'Ácido Úrico',
                    'Glucosa',
                    'Glucosa post-prandial 2H',
                    'Diabetes Gestacional (50g basal y 1H)',
                    'C. Tolerancia a Glucosa 2H',
                    'C. Tolerancia a Glucosa 4H',
                    'Bilirrubina Total y Fracciones',
                    'Proteínas Totales',
                    'Albúmina / Globulina',
                    'Electroforesis de Proteínas',
                    'Cistatina C + Creatinina (TFG)',
                ],
            ],
            'ENZIMAS' => [
                'columna' => 2,
                'items' => [
                    'GGT',
                    'GPT',
                    'GOT',
                    'Fosfatasa Alcalina',
                    'LDH',
                    'Colinesterasa',
                    'Amilasa',
                    'Lipasa',
                    'CPK',
                    'Aldolasa',
                    'Fosfatasa ácida total',
                    'Fosfatasa ácida prostática',
                    'ADA (Adenosín Desaminasa)',
                    'Fibrotest',
                    'Fibromax',
                ],
            ],
            'ELECTROLITOS' => [
                'columna' => 2,
                'items' => [
                    'Sodio',
                    'Potasio',
                    'Cloro',
                    'Calcio',
                    'Calcio Iónico',
                    'Fósforo',
                    'Magnesio',
                    'Amonio',
                    'Litio',
                    'Plomo',
                ],
            ],
            'HECES' => [
                'columna' => 2,
                'items' => [
                    'Parasitológico',
                    'Parasitológico (Concentración)',
                    'Sangre Oculta',
                    'Rotavirus',
                    'Adenovirus',
                    'Ag Helicobacter Pylori',
                    'Coprocultivo',
                    'Cultivo para Hongos',
                    'Moco Fecal (Citología)',
                ],
            ],
            'HORMONAS' => [
                'columna' => 3,
                'items' => [
                    'T3 Total',
                    'T4 Total',
                    'T3 Libre (FT3)',
                    'T4 Libre (FT4)',
                    'TSH',
                    'Tiroglobulina',
                    'Anti-tiroglobulina',
                    'Anti TPO',
                    'Anti Receptores de TSH',
                    'Calcitonina',
                    'FSH',
                    'LH',
                    'Prolactina',
                    'Prolactina Pool',
                    'Progesterona',
                    'Estradiol',
                    'Testosterona',
                    'Testosterona Libre',
                    'Androstenediona',
                    'SHBG',
                    'FAI (Index andrógeno libre)',
                    'DHEAS',
                    '17 Hidroxiprogesterona',
                    'Estriol Libre',
                    'Cortisol AM',
                    'Cortisol PM',
                    'ACTH',
                    'Paratohormona PTH',
                    'Osteocalcina',
                    'Hormona de Crecimiento (GH)',
                    'IGFBP3',
                    'IGF-1',
                    'HCG-Beta Cualitativo',
                    'HCG-Beta Cuantitativo',
                ],
            ],
            'EXUDADO VAGINAL/URETRAL' => [
                'columna' => 3,
                'items' => [
                    'Fresco',
                    'KOH',
                    'Gram',
                    'Cultivo',
                    'Cultivo para Hongos (cándida)',
                    'Estreptococo Grupo B (Identificación)',
                ],
            ],
        ];

        foreach ($catalogo as $nombreGrupo => $info) {
            $grupo = GrupoTipoExamenMedicoocupacional::firstOrCreate(
                [
                    'detalle_grupo' => $nombreGrupo,
                ],
                [
                    'id_usuario_doctor' => $doctorId,
                    'activo' => true,
                ]
            );

            foreach ($info['items'] as $item) {
                TipoExamenMedicoocupacional::firstOrCreate(
                    [
                        'id_grupo_tipo_examen' => $grupo->id,
                        'detalle_tipo' => $item,
                    ],
                    [
                        'id_usuario_doctor' => $doctorId,
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
