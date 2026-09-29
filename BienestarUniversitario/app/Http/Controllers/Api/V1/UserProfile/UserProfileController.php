<?php

namespace App\Http\Controllers\Api\V1\UserProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserProfile\StoreAddressRequest;
use App\Http\Requests\UserProfile\StoreAllergyRequest;
use App\Http\Requests\UserProfile\StoreCareerStudyRequest;
use App\Http\Requests\UserProfile\StoreChildRequest;
use App\Http\Requests\UserProfile\StoreDemographicRequest;
use App\Http\Requests\UserProfile\StoreDisabilityRequest;
use App\Http\Requests\UserProfile\StoreEmergencyContactRequest;
use App\Http\Requests\UserProfile\StoreIdentificationRequest;
use App\Models\ContactoEmergencia;
use App\Models\DatosAutopercepcionCiudadana;
use App\Models\DatosIdentificacion;
use App\Models\DireccionUsuario;
use App\Models\User;
use App\Models\UsuarioEstudiaCarrera;
use App\Models\UsuarioTieneAlergia;
use App\Models\UsuarioTieneDiscapacidad;
use App\Models\UsuarioTieneHijo;
use App\Models\UsuarioTipoSangre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function showIdentification(): JsonResponse
    {
        $user = Auth::user();
        $identificacion = $user->datosIdentificacion;

        return response()->json([
            'data' => $identificacion,
            'message' => $identificacion ? 'Identification data retrieved' : 'No identification data found',
        ]);
    }

    public function storeIdentification(StoreIdentificationRequest $request): JsonResponse
    {
        $user = Auth::user();

        $existing = DatosIdentificacion::where('id_usuario', $user->id)->first();
        $data = $request->validated();

        if ($existing) {
            if ($existing->numero_cedula !== null) {
                $data['numero_cedula'] = $existing->numero_cedula;
            }
            if ($existing->fecha_nacimiento !== null) {
                $data['fecha_nacimiento'] = $existing->fecha_nacimiento;
            }
            $existing->update($data);
            $identificacion = $existing;
        } else {
            $identificacion = DatosIdentificacion::create(array_merge(
                ['id_usuario' => $user->id],
                $data
            ));
        }

        return response()->json([
            'data' => $identificacion,
            'message' => 'Identification data saved successfully',
        ], 201);
    }

    public function updateIdentification(StoreIdentificationRequest $request): JsonResponse
    {
        return $this->storeIdentification($request);
    }

    public function getChildren(): JsonResponse
    {
        $user = Auth::user();
        $hijos = $user->hijos;

        return response()->json([
            'data' => $hijos,
            'message' => 'Children data retrieved',
        ]);
    }

    public function addChild(StoreChildRequest $request): JsonResponse
    {
        $user = Auth::user();

        $hijo = UsuarioTieneHijo::create([
            'id_usuario' => $user->id,
            'numero' => $request->numero,
        ]);

        return response()->json([
            'data' => $hijo,
            'message' => 'Child record added',
        ], 201);
    }

    public function removeChild($id): JsonResponse
    {
        $user = Auth::user();
        $hijo = UsuarioTieneHijo::where('id', $id)
            ->where('id_usuario', $user->id)
            ->first();

        if (!$hijo) {
            return response()->json([
                'message' => 'Child record not found',
            ], 404);
        }

        $hijo->delete();

        return response()->json(null, 204);
    }

    public function getAllergies(): JsonResponse
    {
        $user = Auth::user();
        $alergias = $user->alergias;

        return response()->json([
            'data' => $alergias,
            'message' => 'Allergies data retrieved',
        ]);
    }

    public function addAllergy(StoreAllergyRequest $request): JsonResponse
    {
        $user = Auth::user();

        $alergia = UsuarioTieneAlergia::create([
            'id_usuario' => $user->id,
            'detalle_alergia' => $request->detalle_alergia,
        ]);

        return response()->json([
            'data' => $alergia,
            'message' => 'Allergy record added',
        ], 201);
    }

    public function removeAllergy($id): JsonResponse
    {
        $user = Auth::user();
        $alergia = UsuarioTieneAlergia::where('id', $id)
            ->where('id_usuario', $user->id)
            ->first();

        if (!$alergia) {
            return response()->json([
                'message' => 'Allergy record not found',
            ], 404);
        }

        $alergia->delete();

        return response()->json(null, 204);
    }

    public function getDisabilities(): JsonResponse
    {
        $user = Auth::user();
        $discapacidades = $user->discapacidades;

        return response()->json([
            'data' => $discapacidades,
            'message' => 'Disabilities data retrieved',
        ]);
    }

    public function addDisability(StoreDisabilityRequest $request): JsonResponse
    {
        $user = Auth::user();

        $discapacidad = UsuarioTieneDiscapacidad::create([
            'id_usuario' => $user->id,
            'detalle_discapacidad' => $request->detalle_discapacidad,
        ]);

        return response()->json([
            'data' => $discapacidad,
            'message' => 'Disability record added',
        ], 201);
    }

    public function removeDisability($id): JsonResponse
    {
        $user = Auth::user();
        $discapacidad = UsuarioTieneDiscapacidad::where('id', $id)
            ->where('id_usuario', $user->id)
            ->first();

        if (!$discapacidad) {
            return response()->json([
                'message' => 'Disability record not found',
            ], 404);
        }

        $discapacidad->delete();

        return response()->json(null, 204);
    }

    public function showCareerStudy(): JsonResponse
    {
        $user = Auth::user();
        $carrera = $user->estudioCarrera;

        return response()->json([
            'data' => $carrera ? $carrera->load(['facultad', 'carrera', 'ciclo', 'tipoUsuario']) : null,
            'message' => $carrera ? 'Career study data retrieved' : 'No career study data found',
        ]);
    }

    public function storeCareerStudy(StoreCareerStudyRequest $request): JsonResponse
    {
        $user = Auth::user();

        $carrera = UsuarioEstudiaCarrera::updateOrCreate(
            ['id_usuario' => $user->id],
            $request->validated()
        );

        return response()->json([
            'data' => $carrera->load(['facultad', 'carrera', 'ciclo', 'tipoUsuario']),
            'message' => 'Career study data saved successfully',
        ], 201);
    }

    public function showDemographic(): JsonResponse
    {
        $user = Auth::user();
        $demografico = $user->autopercepcion;

        return response()->json([
            'data' => $demografico ? $demografico->load(['identificacionEtnica', 'genero', 'estadoCivil']) : null,
            'message' => $demografico ? 'Demographic data retrieved' : 'No demographic data found',
        ]);
    }

    public function storeDemographic(StoreDemographicRequest $request): JsonResponse
    {
        $user = Auth::user();

        $demografico = DatosAutopercepcionCiudadana::updateOrCreate(
            ['id_usuario' => $user->id],
            $request->validated()
        );

        return response()->json([
            'data' => $demografico->load(['identificacionEtnica', 'genero', 'estadoCivil']),
            'message' => 'Demographic data saved successfully',
        ], 201);
    }

    public function getAddresses(): JsonResponse
    {
        $user = Auth::user();
        $direcciones = $user->direcciones()->with(['provincia', 'canton', 'tipoDireccion'])->get();

        return response()->json([
            'data' => $direcciones,
            'message' => 'Addresses retrieved',
        ]);
    }

    public function storeAddress(StoreAddressRequest $request): JsonResponse
    {
        $user = Auth::user();

        $direccion = DireccionUsuario::updateOrCreate(
            [
                'id_usuario' => $user->id,
                'id_tipo_direccion' => $request->id_tipo_direccion,
            ],
            $request->validated()
        );

        return response()->json([
            'data' => $direccion->load(['provincia', 'canton', 'tipoDireccion']),
            'message' => 'Address saved successfully',
        ], 201);
    }

    public function updateAddress(StoreAddressRequest $request, $id): JsonResponse
    {
        $user = Auth::user();
        $direccion = DireccionUsuario::where('id', $id)
            ->where('id_usuario', $user->id)
            ->first();

        if (!$direccion) {
            return response()->json([
                'message' => 'Address not found',
            ], 404);
        }

        $direccion->update($request->validated());

        return response()->json([
            'data' => $direccion->load(['provincia', 'canton', 'tipoDireccion']),
            'message' => 'Address updated successfully',
        ]);
    }

    public function removeAddress($id): JsonResponse
    {
        $user = Auth::user();
        $direccion = DireccionUsuario::where('id', $id)
            ->where('id_usuario', $user->id)
            ->first();

        if (!$direccion) {
            return response()->json([
                'message' => 'Address not found',
            ], 404);
        }

        $direccion->delete();

        return response()->json(null, 204);
    }

    public function getEmergencyContacts(): JsonResponse
    {
        $user = Auth::user();
        $contactos = $user->contactosEmergencia;

        return response()->json([
            'data' => $contactos,
            'message' => 'Emergency contacts retrieved',
        ]);
    }

    public function storeEmergencyContact(StoreEmergencyContactRequest $request): JsonResponse
    {
        $user = Auth::user();

        $contacto = ContactoEmergencia::create([
            'id_usuario' => $user->id,
            ...$request->validated(),
        ]);

        return response()->json([
            'data' => $contacto,
            'message' => 'Emergency contact created successfully',
        ], 201);
    }

    public function updateEmergencyContact(StoreEmergencyContactRequest $request, $id): JsonResponse
    {
        $user = Auth::user();
        $contacto = ContactoEmergencia::where('id', $id)
            ->where('id_usuario', $user->id)
            ->first();

        if (!$contacto) {
            return response()->json([
                'message' => 'Emergency contact not found',
            ], 404);
        }

        $contacto->update($request->validated());

        return response()->json([
            'data' => $contacto,
            'message' => 'Emergency contact updated successfully',
        ]);
    }

    public function removeEmergencyContact($id): JsonResponse
    {
        $user = Auth::user();
        $contacto = ContactoEmergencia::where('id', $id)
            ->where('id_usuario', $user->id)
            ->first();

        if (!$contacto) {
            return response()->json([
                'message' => 'Emergency contact not found',
            ], 404);
        }

        $contacto->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // BLOOD TYPE
    // ==========================================

    public function getBloodType(): JsonResponse
    {
        $user = Auth::user();
        $actual = $user->tipoSangreActual()->with('tipoSangre')->first();

        return response()->json([
            'data' => $actual,
            'message' => $actual ? 'Blood type retrieved' : 'No blood type assigned',
        ]);
    }

    public function storeBloodType(Request $request): JsonResponse
    {
        $request->validate([
            'id_tipo_sangre' => 'required|integer|exists:tipo_sangre,id',
        ]);

        $user = Auth::user();

        $registro = UsuarioTipoSangre::create([
            'id_usuario' => $user->id,
            'id_tipo_sangre' => $request->id_tipo_sangre,
            'asignado_por_usuario' => null,
            'asignado_por_rol' => 'paciente',
        ]);

        return response()->json([
            'data' => $registro->load('tipoSangre'),
            'message' => 'Blood type saved successfully',
        ], 201);
    }

    public function getProfile(): JsonResponse
    {
        $user = Auth::user();
        $user->load([
            'datosIdentificacion',
            'hijos',
            'alergias',
            'discapacidades',
            'estudioCarrera.facultad',
            'estudioCarrera.carrera',
            'estudioCarrera.ciclo',
            'estudioCarrera.tipoUsuario',
            'autopercepcion.identificacionEtnica',
            'autopercepcion.genero',
            'autopercepcion.estadoCivil',
            'direcciones.provincia',
            'direcciones.canton',
            'direcciones.tipoDireccion',
            'contactosEmergencia',
            'foto',
            'tipoSangreActual.tipoSangre',
        ]);

        return response()->json([
            'data' => [
                'identification' => $user->datosIdentificacion,
                'children' => $user->hijos,
                'allergies' => $user->alergias,
                'disabilities' => $user->discapacidades,
                'career_study' => $user->estudioCarrera,
                'demographic' => $user->autopercepcion,
                'addresses' => $user->direcciones,
                'emergency_contacts' => $user->contactosEmergencia,
                'photo' => $user->foto,
                'blood_type' => $user->tipoSangreActual?->tipoSangre,
            ],
            'message' => 'Full profile retrieved',
        ]);
    }

    public function getCatalogos(): JsonResponse
    {
        return response()->json([
            'facultades' => \App\Models\Facultad::orderBy('nombre')->get(['id', 'nombre']),
            'carreras' => \App\Models\Carrera::orderBy('nombre')->get(['id', 'nombre', 'id_facultad']),
            'ciclos' => \App\Models\Ciclo::orderBy('numero')->get(['id', 'numero']),
            'tipos_usuario' => \App\Models\TipoUsuario::orderBy('nombre')->get(['id', 'nombre']),
            'etnias' => \App\Models\IdentificacionEtnica::orderBy('nombre')->get(['id', 'nombre']),
            'generos' => \App\Models\IdentificacionGenero::orderBy('nombre')->get(['id', 'nombre']),
            'estados_civil' => \App\Models\IdentificacionEstadoCivil::orderBy('nombres')->get(['id', 'nombres as nombre']),
            'provincias' => \App\Models\Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'cantones' => \App\Models\Canton::orderBy('nombre')->get(['id', 'nombre', 'id_provincia']),
            'tipos_direccion' => \App\Models\TipoDireccion::orderBy('nombre')->get(['id', 'nombre']),
            'tipos_sangre' => \App\Models\TipoSangre::orderBy('id')->get(['id', 'nombre']),
        ]);
    }

    public function getPatientProfile(int $id): JsonResponse
    {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Paciente no encontrado'], 404);
        }

        $user->load([
            'datosIdentificacion',
            'hijos',
            'alergias',
            'discapacidades',
            'estudioCarrera.facultad',
            'estudioCarrera.carrera',
            'estudioCarrera.ciclo',
            'estudioCarrera.tipoUsuario',
            'autopercepcion.identificacionEtnica',
            'autopercepcion.genero',
            'autopercepcion.estadoCivil',
            'direcciones.provincia',
            'direcciones.canton',
            'direcciones.tipoDireccion',
            'contactosEmergencia',
            'foto',
            'tipoSangreActual.tipoSangre',
        ]);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'id_tipo_usuario' => $user->id_tipo_usuario,
                'datos_identificacion' => $user->datosIdentificacion,
                'identification' => $user->datosIdentificacion,
                'children' => $user->hijos,
                'hijos' => $user->hijos,
                'allergies' => $user->alergias,
                'alergias' => $user->alergias,
                'disabilities' => $user->discapacidades,
                'discapacidades' => $user->discapacidades,
                'career_study' => $user->estudioCarrera,
                'estudio_carrera' => $user->estudioCarrera,
                'estudioCarrera' => $user->estudioCarrera,
                'demographic' => $user->autopercepcion,
                'autopercepcion' => $user->autopercepcion,
                'addresses' => $user->direcciones,
                'direcciones' => $user->direcciones,
                'emergency_contacts' => $user->contactosEmergencia,
                'contactos_emergencia' => $user->contactosEmergencia,
                'photo' => $user->foto,
                'foto' => $user->foto,
                'blood_type' => $user->tipoSangreActual?->tipoSangre,
            ],
            'message' => 'Patient profile retrieved',
        ]);
    }
}