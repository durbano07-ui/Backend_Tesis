<?php

namespace Database\Seeders;

use App\Models\Canton;
use App\Models\Carrera;
use App\Models\Ciclo;
use App\Models\CondicionLaboral;
use App\Models\Facultad;
use App\Models\IdentificacionEstadoCivil;
use App\Models\IdentificacionEtnica;
use App\Models\IdentificacionGenero;
use App\Models\LugarTrabajo;
use App\Models\OdontogramaOdontologia;
use App\Models\OdontogramaPiezaCarilla;
use App\Models\Provincia;
use App\Models\TipoDireccion;
use App\Models\TipoUsuario;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->identificacionEtnica();
        $this->identificacionGenero();
        $this->identificacionEstadoCivil();
        $this->provinciasYCantones();
        $this->facultades();
        $this->carreras();
        $this->ciclos();
        $this->tipoDireccion();
        $this->tipoUsuario();
        $this->condicionLaboral();
        $this->lugarTrabajo();
        $this->odontograma();
    }

    private function identificacionEtnica(): void
    {
        $data = [
            'Mestizo',
            'Indígena',
            'Afroecuatoriano',
            'Montubio',
            'Blanco',
            'Otro',
        ];

        foreach ($data as $nombre) {
            IdentificacionEtnica::create(['nombre' => $nombre]);
        }
    }

    private function identificacionGenero(): void
    {
        $data = [
            'Masculino',
            'Femenino',
            'LGBTI',
        ];

        foreach ($data as $nombre) {
            IdentificacionGenero::create(['nombre' => $nombre]);
        }
    }

    private function identificacionEstadoCivil(): void
    {
        $data = [
            'Soltero',
            'Casado',
            'Viudo',
            'Divorciado',
            'Unión Libre',
        ];

        foreach ($data as $nombres) {
            IdentificacionEstadoCivil::create(['nombres' => $nombres]);
        }
    }

    private function provinciasYCantones(): void
    {
        $provincias = [
            'Azuay' => ['Cuenca', 'Girón', 'Gualaceo', 'Nabón', 'Paute', 'Pucará', 'San Fernando', 'Santa Isabel', 'Sigsig', 'Oña', 'Chordeleg', 'El Pan', 'Sevilla de Oro', 'Guachapala', 'Camilo Ponce Enríquez'],
            'Bolívar' => ['Guaranda', 'Chillanes', 'Chimbo', 'Echeandía', 'San Miguel', 'Caluma', 'Las Naves'],
            'Cañar' => ['Azogues', 'Biblián', 'Cañar', 'La Troncal', 'El Tambo', 'Déleg', 'Suscal'],
            'Carchi' => ['Tulcán', 'Bolívar', 'Espejo', 'Mira', 'Montúfar', 'San Pedro de Huaca'],
            'Cotopaxi' => ['Latacunga', 'La Maná', 'Pangua', 'Pujilí', 'Salcedo', 'Saquisilí', 'Sigchos'],
            'Chimborazo' => ['Riobamba', 'Alausí', 'Colta', 'Chambo', 'Chunchi', 'Guamote', 'Guano', 'Pallatanga', 'Penipe', 'Cumandá'],
            'El Oro' => ['Machala', 'Arenillas', 'Atahualpa', 'Balsas', 'Chilla', 'El Guabo', 'Huaquillas', 'Las Lajas', 'Marcabelí', 'Pasaje', 'Piñas', 'Portovelo', 'Santa Rosa', 'Zaruma'],
            'Esmeraldas' => ['Esmeraldas', 'Eloy Alfaro', 'Muisne', 'Quinindé', 'San Lorenzo', 'Atacames', 'Rioverde'],
            'Guayas' => ['Guayaquil', 'Alfredo Baquerizo Moreno (Juján)', 'Balao', 'Balzar', 'Colimes', 'Daule', 'Durán', 'El Empalme', 'El Triunfo', 'Milagro', 'Naranjal', 'Naranjito', 'Palestina', 'Pedro Carbo', 'Samborondón', 'Santa Lucía', 'Salitre', 'San Jacinto de Yaguachi', 'Playas', 'Simón Bolívar', 'Coronel Marcelino Maridueña', 'Lomas de Sargentillo', 'Nobol', 'General Antonio Elizalde', 'Isidro Ayora'],
            'Imbabura' => ['Ibarra', 'Antonio Ante', 'Cotacachi', 'Otavalo', 'Pimampiro', 'San Miguel de Urcuquí'],
            'Loja' => ['Loja', 'Calvas', 'Catamayo', 'Celica', 'Chaguarpamba', 'Espíndola', 'Gonzanamá', 'Macará', 'Paltas', 'Puyango', 'Saraguro', 'Sozoranga', 'Zapotillo', 'Pindal', 'Quilanga', 'Olmedo'],
            'Los Ríos' => ['Babahoyo', 'Baba', 'Montalvo', 'Puebloviejo', 'Quevedo', 'Urdaneta', 'Ventanas', 'Vinces', 'Palenque', 'Buena Fe', 'Valencia', 'Mocache', 'Quinsaloma'],
            'Manabí' => ['Portoviejo', 'Bolívar', 'Chone', 'El Carmen', 'Flavio Alfaro', 'Jipijapa', 'Junín', 'Manta', 'Montecristi', 'Paján', 'Pichincha', 'Rocafuerte', 'Santa Ana', 'Sucre', 'Tosagua', '24 de Mayo', 'Pedernales', 'Olmedo', 'Puerto López', 'Jama', 'Jaramijó', 'San Vicente'],
            'Morona Santiago' => ['Morona', 'Gualaquiza', 'Limón Indanza', 'Palora', 'Sucúa', 'Huamboya', 'San Juan Bosco', 'Taisha', 'Logroño', 'Pablo Sexto', 'Tiwintza', 'Santiago'],
            'Napo' => ['Tena', 'Archidona', 'El Chaco', 'Quijos', 'Carlos Julio Arosemena Tola'],
            'Pastaza' => ['Pastaza', 'Mera', 'Santa Clara', 'Arajuno'],
            'Pichincha' => ['Quito', 'Cayambe', 'Mejía', 'Pedro Moncayo', 'Rumiñahui', 'San Miguel de los Bancos', 'Pedro Vicente Maldonado', 'Puerto Quito'],
            'Tungurahua' => ['Ambato', 'Baños de Agua Santa', 'Cevallos', 'Mocha', 'Patate', 'Quero', 'Pelileo', 'Píllaro', 'Tisaleo'],
            'Zamora Chinchipe' => ['Zamora', 'Chinchipe', 'Nangaritza', 'Yacuambi', 'Yantzaza', 'El Pangui', 'Palanda', 'Centinela del Cóndor', 'Paquisha'],
            'Galápagos' => ['San Cristóbal', 'Santa Cruz', 'Isabela'],
            'Sucumbíos' => ['Lago Agrio', 'Gonzalo Pizarro', 'Putumayo', 'Shushufindi', 'Sucumbíos', 'Cascales', 'Cuyabeno'],
            'Orellana' => ['Orellana', 'Aguarico', 'La Joya de los Sachas', 'Loreto'],
            'Santo Domingo de los Tsáchilas' => ['Santo Domingo', 'La Concordia'],
            'Santa Elena' => ['Santa Elena', 'La Libertad', 'Salinas'],
        ];

        foreach ($provincias as $provincia => $cantones) {
            $prov = Provincia::create(['nombre' => $provincia]);
            foreach ($cantones as $canton) {
                Canton::create(['id_provincia' => $prov->id, 'nombre' => $canton]);
            }
        }
    }

    private function facultades(): void
    {
        $facultades = [
            'Facultad de Ciencias Administrativas, Gestión Empresarial e Informática',
            'Facultad de Jurisprudencia, Ciencias Sociales y Políticas',
            'Facultad de Ciencias de la Educación, Sociales, Filosóficas y Humanísticas',
            'Facultad de Ciencias Agropecuarias, Recursos Naturales y del Ambiente',
            'Facultad de Ciencias de la Salud y del Ser Humano',
            'Extensión Universitaria de San Miguel',
        ];

        foreach ($facultades as $nombre) {
            Facultad::create(['nombre' => $nombre]);
        }
    }

    private function carreras(): void
    {
        $carreras = [
            // Facultad 1 - Ciencias Administrativas
            ['id_facultad' => 1, 'nombre' => 'Administración de Empresas'],
            ['id_facultad' => 1, 'nombre' => 'Comunicación'],
            ['id_facultad' => 1, 'nombre' => 'Contabilidad y Auditoría'],
            ['id_facultad' => 1, 'nombre' => 'Emprendimiento e Innovación Social'],
            ['id_facultad' => 1, 'nombre' => 'Marketing Digital'],
            ['id_facultad' => 1, 'nombre' => 'Mercadotecnia'],
            ['id_facultad' => 1, 'nombre' => 'Software'],
            ['id_facultad' => 1, 'nombre' => 'Tecnologías de la Información'],
            ['id_facultad' => 1, 'nombre' => 'Turismo y Hotelería'],
            // Facultad 2 - Jurisprudencia
            ['id_facultad' => 2, 'nombre' => 'Derecho'],
            ['id_facultad' => 2, 'nombre' => 'Sociología'],
            // Facultad 3 - Educación
            ['id_facultad' => 3, 'nombre' => 'Educación Básica'],
            ['id_facultad' => 3, 'nombre' => 'Educación Inicial'],
            ['id_facultad' => 3, 'nombre' => 'Educación Intercultural Bilingüe'],
            ['id_facultad' => 3, 'nombre' => 'Pedagogía de los Idiomas Nacionales y Extranjeros'],
            ['id_facultad' => 3, 'nombre' => 'Pedagogía de la Matemática y la Física'],
            ['id_facultad' => 3, 'nombre' => 'Pedagogía de las Ciencias Experimentales - Informática'],
            // Facultad 4 - Agropecuarias
            ['id_facultad' => 4, 'nombre' => 'Agroindustrias'],
            ['id_facultad' => 4, 'nombre' => 'Agronomía'],
            ['id_facultad' => 4, 'nombre' => 'Medicina Veterinaria'],
            // Facultad 5 - Salud
            ['id_facultad' => 5, 'nombre' => 'Enfermería'],
            ['id_facultad' => 5, 'nombre' => 'Ingeniería en Riesgos de Desastres'],
            ['id_facultad' => 5, 'nombre' => 'Psicología'],
            ['id_facultad' => 5, 'nombre' => 'Terapia Física'],
            // Extensión San Miguel
            ['id_facultad' => 6, 'nombre' => 'Criminalística'],
            ['id_facultad' => 6, 'nombre' => 'Gestión del Talento Humano'],
        ];

        foreach ($carreras as $carrera) {
            Carrera::create($carrera);
        }
    }

    private function ciclos(): void
    {
        $ciclos = [
            'Primero',
            'Segundo',
            'Tercero',
            'Cuarto',
            'Quinto',
            'Sexto',
            'Séptimo',
            'Octavo',
        ];

        foreach ($ciclos as $numero) {
            Ciclo::create(['numero' => $numero]);
        }
    }

    private function tipoDireccion(): void
    {
        $tipos = [
            'Procedencia',
            'Actual',
            'Nacimiento',
        ];

        foreach ($tipos as $nombre) {
            TipoDireccion::create(['nombre' => $nombre]);
        }
    }

    private function tipoUsuario(): void
    {
        $tipos = [
            'Médico',
            'Estudiante',
            'Docente',
            'Administrativo',
            'Código de Trabajo',
        ];

        foreach ($tipos as $nombre) {
            TipoUsuario::create(['nombre' => $nombre]);
        }
    }

    private function condicionLaboral(): void
    {
        $condiciones = [
            'Contratado Ocasional',
            'Nombramiento',
            'Contrato de servicios',
            'Contrato indefinido',
        ];

        foreach ($condiciones as $nombre) {
            CondicionLaboral::create(['nombre' => $nombre]);
        }
    }

    private function lugarTrabajo(): void
    {
        $lugares = [
            'Campus Guanujo',
            'Campus Laguacoto',
            'Campus San Miguel',
            'Campus Chimbo',
        ];

        foreach ($lugares as $nombre) {
            LugarTrabajo::create(['nombre' => $nombre]);
        }
    }

    private function odontograma(): void
    {
        // Pieces for adult dentition (Universal Numbering System)
        // Upper right: 1-8 (central incisor to third molar)
        // Upper left: 9-16 (third molar to central incisor)
        // Lower left: 17-24 (third molar to central incisor)
        // Lower right: 25-32 (central incisor to third molar)

        $piezasPermanentes = [
            // Upper right (1-8)
            1, 2, 3, 4, 5, 6, 7, 8,
            // Upper left (9-16)
            9, 10, 11, 12, 13, 14, 15, 16,
            // Lower left (17-24)
            17, 18, 19, 20, 21, 22, 23, 24,
            // Lower right (25-32)
            25, 26, 27, 28, 29, 30, 31, 32,
        ];

        foreach ($piezasPermanentes as $numero) {
            $pieza = OdontogramaOdontologia::create(['numero_pieza_dental' => $numero]);

            // 5 surfaces per tooth: mesial, distal, oclusal, lingual/palatal, vestibular
            $carillas = ['Mesial', 'Distal', 'Oclusal', 'Lingual/Palatal', 'Vestibular'];
            foreach ($carillas as $numeroCarilla) {
                OdontogramaPiezaCarilla::create([
                    'id_numero_pieza' => $pieza->id,
                    'numero_carilla' => $numeroCarilla,
                ]);
            }
        }

        // Also add primary teeth (baby teeth) - letters A-T
        $piezasPrimarias = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
        foreach ($piezasPrimarias as $letra) {
            $pieza = OdontogramaOdontologia::create(['numero_pieza_dental' => $letra]);
            $carillas = ['Mesial', 'Distal', 'Oclusal', 'Lingual/Palatal', 'Vestibular'];
            foreach ($carillas as $numeroCarilla) {
                OdontogramaPiezaCarilla::create([
                    'id_numero_pieza' => $pieza->id,
                    'numero_carilla' => $numeroCarilla,
                ]);
            }
        }
    }
}
