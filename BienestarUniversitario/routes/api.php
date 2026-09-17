<?php

use App\Http\Controllers\Api\V1\CampusManagementController;
use App\Http\Controllers\Api\V1\CitasMedicasController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Enfermeria\EnfermeriaController;
use App\Http\Controllers\Api\V1\Farmacia\FarmaciaController;
use App\Http\Controllers\Api\V1\Farmacia\PresentacionController;
use App\Http\Controllers\Api\V1\LoginAttemptController;
use App\Http\Controllers\Api\V1\MedicalStaffController;
use App\Http\Controllers\Api\V1\MedicinaGeneral\MedicinaGeneralController;
use App\Http\Controllers\Api\V1\MedicinaOcupacional\MedicinaOcupacionalController;
use App\Http\Controllers\Api\V1\Odontologia\OdontologiaController;
use App\Http\Controllers\Api\V1\Psicologia\PsicologiaController;
use App\Http\Controllers\Api\V1\ReportesController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SecurityLogController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserProfile\UserProfileController;
use App\Http\Controllers\Api\V1\UserProfile\UserProfilePhotoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    // Auth routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Protected auth routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // Patient profile endpoints for medical staff
        Route::get('/medicina-general/pacientes/{id}/perfil', [UserProfileController::class, 'getPatientProfile']);
        Route::get('/psicologia/pacientes/{id}/perfil', [UserProfileController::class, 'getPatientProfile']);
        Route::get('/odontologia/pacientes/{id}/perfil', [UserProfileController::class, 'getPatientProfile']);

        // Auth management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::put('/password', [AuthController::class, 'changePassword']);
        });

        // User management (requires roles)
        Route::middleware(['force_password_change'])->group(function () {
            // Users
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::post('/users/register-patient', [UserController::class, 'registerPatient']);
            Route::get('/users/search-by-cedula', [UserController::class, 'searchByCedula']);
            Route::get('/users/{user}', [UserController::class, 'show']);
            Route::put('/users/{user}', [UserController::class, 'update']);
            Route::put('/users/{user}/disable', [UserController::class, 'disable']);
            Route::put('/users/{user}/enable', [UserController::class, 'enable']);
            Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
            Route::delete('/users/{user}/roles/{role}', [UserController::class, 'removeRole']);

            // Roles
            Route::get('/roles', [RoleController::class, 'index']);

            // Audit logs
            Route::get('/audit-logs', [AuditLogController::class, 'index']);

            // Dashboard (coordinator and admin)
            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/dashboard/doctores', [DashboardController::class, 'doctores']);
            Route::get('/dashboard/estudiantes-frecuentes', [DashboardController::class, 'estudiantesFrecuentes']);
            Route::get('/dashboard/atenciones-tiempo', [DashboardController::class, 'atencionesPorTiempo']);
            Route::get('/dashboard/auditoria', [DashboardController::class, 'auditoria'])
                ->middleware('role:administrador');

            // Gestión Campus, Facultades y Carreras (CRUD)
            Route::prefix('campus-management')->group(function () {
                // Campus
                Route::get('/campus', [CampusManagementController::class, 'indexCampus']);
                Route::post('/campus', [CampusManagementController::class, 'storeCampus']);
                Route::put('/campus/{id}', [CampusManagementController::class, 'updateCampus']);
                Route::delete('/campus/{id}', [CampusManagementController::class, 'destroyCampus']);

                // Facultades
                Route::get('/facultades', [CampusManagementController::class, 'indexFacultades']);
                Route::post('/facultades', [CampusManagementController::class, 'storeFacultad']);
                Route::put('/facultades/{id}', [CampusManagementController::class, 'updateFacultad']);
                Route::delete('/facultades/{id}', [CampusManagementController::class, 'destroyFacultad']);

                // Carreras
                Route::get('/carreras', [CampusManagementController::class, 'indexCarreras']);
                Route::post('/carreras', [CampusManagementController::class, 'storeCarrera']);
                Route::put('/carreras/{id}', [CampusManagementController::class, 'updateCarrera']);
                Route::delete('/carreras/{id}', [CampusManagementController::class, 'destroyCarrera']);
            });
        });

        // Admin-only routes
        Route::middleware(['force_password_change', 'role:administrador'])->group(function () {
            Route::get('/security-logs', [SecurityLogController::class, 'index']);
            Route::get('/security-logs/blocked-ips', [SecurityLogController::class, 'blockedIps']);
            Route::get('/login-attempts', [LoginAttemptController::class, 'index']);
        });
    });
});

// User Profile (authenticated)
Route::prefix('v1/user-profile')->middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'getProfile']);
    Route::get('/catalogos', [UserProfileController::class, 'getCatalogos']);

    // Identification
    Route::get('/identification', [UserProfileController::class, 'showIdentification']);
    Route::post('/identification', [UserProfileController::class, 'storeIdentification']);
    Route::put('/identification', [UserProfileController::class, 'updateIdentification']);

    // Children
    Route::get('/children', [UserProfileController::class, 'getChildren']);
    Route::post('/children', [UserProfileController::class, 'addChild']);
    Route::delete('/children/{id}', [UserProfileController::class, 'removeChild']);

    // Allergies
    Route::get('/allergies', [UserProfileController::class, 'getAllergies']);
    Route::post('/allergies', [UserProfileController::class, 'addAllergy']);
    Route::delete('/allergies/{id}', [UserProfileController::class, 'removeAllergy']);

    // Disabilities
    Route::get('/disabilities', [UserProfileController::class, 'getDisabilities']);
    Route::post('/disabilities', [UserProfileController::class, 'addDisability']);
    Route::delete('/disabilities/{id}', [UserProfileController::class, 'removeDisability']);

    // Career Study
    Route::get('/career-study', [UserProfileController::class, 'showCareerStudy']);
    Route::post('/career-study', [UserProfileController::class, 'storeCareerStudy']);

    // Demographic
    Route::get('/demographic', [UserProfileController::class, 'showDemographic']);
    Route::post('/demographic', [UserProfileController::class, 'storeDemographic']);

    // Addresses
    Route::get('/addresses', [UserProfileController::class, 'getAddresses']);
    Route::post('/addresses', [UserProfileController::class, 'storeAddress']);
    Route::put('/addresses/{id}', [UserProfileController::class, 'updateAddress']);
    Route::delete('/addresses/{id}', [UserProfileController::class, 'removeAddress']);

    // Emergency Contacts
    Route::get('/emergency-contacts', [UserProfileController::class, 'getEmergencyContacts']);
    Route::post('/emergency-contacts', [UserProfileController::class, 'storeEmergencyContact']);
    Route::put('/emergency-contacts/{id}', [UserProfileController::class, 'updateEmergencyContact']);
    Route::delete('/emergency-contacts/{id}', [UserProfileController::class, 'removeEmergencyContact']);

    // Photo
    Route::get('/photo', [UserProfilePhotoController::class, 'show']);
    Route::post('/photo', [UserProfilePhotoController::class, 'store']);

    // Blood Type
    Route::get('/blood-type', [UserProfileController::class, 'getBloodType']);
    Route::post('/blood-type', [UserProfileController::class, 'storeBloodType']);
});

// Medical Staff
Route::prefix('v1/medical-staff')->middleware('auth:sanctum')->group(function () {
    Route::get('/cargos', [MedicalStaffController::class, 'getCargos']);
    Route::post('/cargos', [MedicalStaffController::class, 'storeCargo']);
    Route::get('/mis-pacientes', [MedicalStaffController::class, 'getMisPacientes']);
    Route::post('/asignar-paciente', [MedicalStaffController::class, 'asignarPaciente']);
    Route::delete('/desasignar-paciente/{id}', [MedicalStaffController::class, 'desasignarPaciente']);
    Route::get('/doctores', [MedicalStaffController::class, 'getDoctores']);
});

// Enfermeria
Route::prefix('v1/enfermeria')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    // Procedimientos
    Route::get('/procedimientos', [EnfermeriaController::class, 'indexProcedimientos']);
    Route::get('/procedimientos/{id}', [EnfermeriaController::class, 'showProcedimiento']);
    Route::post('/procedimientos', [EnfermeriaController::class, 'storeProcedimiento']);
    Route::put('/procedimientos/{id}', [EnfermeriaController::class, 'updateProcedimiento']);
    Route::delete('/procedimientos/{id}', [EnfermeriaController::class, 'destroyProcedimiento']);

    // Signos Vitales
    Route::get('/signos-vitales', [EnfermeriaController::class, 'indexSignosVitales']);
    Route::get('/signos-vitales/{id}', [EnfermeriaController::class, 'showSignosVitales']);
    Route::post('/signos-vitales', [EnfermeriaController::class, 'storeSignosVitales']);
    Route::put('/signos-vitales/{id}', [EnfermeriaController::class, 'updateSignosVitales']);
    Route::delete('/signos-vitales/{id}', [EnfermeriaController::class, 'destroySignosVitales']);

    // Parte Diario de Enfermeria
    Route::get('/parte-diario', [EnfermeriaController::class, 'indexParteDiario']);
    Route::get('/parte-diario/{id}', [EnfermeriaController::class, 'showParteDiario']);
    Route::post('/parte-diario', [EnfermeriaController::class, 'storeParteDiario']);
    Route::put('/parte-diario/{id}', [EnfermeriaController::class, 'updateParteDiario']);
    Route::delete('/parte-diario/{id}', [EnfermeriaController::class, 'destroyParteDiario']);
});

// Medicina General
Route::prefix('v1/medicina-general')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    // Signos Vitales
    Route::get('/signos-vitales', [MedicinaGeneralController::class, 'indexSignosVitales']);
    Route::get('/signos-vitales/{id}', [MedicinaGeneralController::class, 'showSignosVitales']);
    Route::post('/signos-vitales', [MedicinaGeneralController::class, 'storeSignosVitales']);
    Route::put('/signos-vitales/{id}', [MedicinaGeneralController::class, 'updateSignosVitales']);
    Route::delete('/signos-vitales/{id}', [MedicinaGeneralController::class, 'destroySignosVitales']);

    // Parte Diario Medicina
    Route::get('/parte-diario', [MedicinaGeneralController::class, 'indexParteDiario']);
    Route::get('/parte-diario/{id}', [MedicinaGeneralController::class, 'showParteDiario']);
    Route::post('/parte-diario', [MedicinaGeneralController::class, 'storeParteDiario']);
    Route::put('/parte-diario/{id}', [MedicinaGeneralController::class, 'updateParteDiario']);
    Route::delete('/parte-diario/{id}', [MedicinaGeneralController::class, 'destroyParteDiario']);

    // Motivo Consulta
    Route::get('/motivo-consulta', [MedicinaGeneralController::class, 'indexMotivoConsulta']);
    Route::get('/motivo-consulta/{id}', [MedicinaGeneralController::class, 'showMotivoConsulta']);
    Route::post('/motivo-consulta', [MedicinaGeneralController::class, 'storeMotivoConsulta']);
    Route::put('/motivo-consulta/{id}', [MedicinaGeneralController::class, 'updateMotivoConsulta']);
    Route::delete('/motivo-consulta/{id}', [MedicinaGeneralController::class, 'destroyMotivoConsulta']);

    // Antecedentes
    Route::get('/antecedentes', [MedicinaGeneralController::class, 'indexAntecedentes']);
    Route::get('/antecedentes/{id}', [MedicinaGeneralController::class, 'showAntecedente']);
    Route::post('/antecedentes', [MedicinaGeneralController::class, 'storeAntecedente']);
    Route::put('/antecedentes/{id}', [MedicinaGeneralController::class, 'updateAntecedente']);
    Route::delete('/antecedentes/{id}', [MedicinaGeneralController::class, 'destroyAntecedente']);

    // Enfermedades Actuales
    Route::get('/enfermedades-actuales', [MedicinaGeneralController::class, 'indexEnfermedadesActuales']);
    Route::get('/enfermedades-actuales/{id}', [MedicinaGeneralController::class, 'showEnfermedadActual']);
    Route::post('/enfermedades-actuales', [MedicinaGeneralController::class, 'storeEnfermedadActual']);
    Route::put('/enfermedades-actuales/{id}', [MedicinaGeneralController::class, 'updateEnfermedadActual']);
    Route::delete('/enfermedades-actuales/{id}', [MedicinaGeneralController::class, 'destroyEnfermedadActual']);

    // Revision Organos
    Route::get('/revision-organos', [MedicinaGeneralController::class, 'indexRevisionOrganos']);
    Route::get('/revision-organos/{id}', [MedicinaGeneralController::class, 'showRevisionOrganos']);
    Route::post('/revision-organos', [MedicinaGeneralController::class, 'storeRevisionOrganos']);
    Route::put('/revision-organos/{id}', [MedicinaGeneralController::class, 'updateRevisionOrganos']);
    Route::delete('/revision-organos/{id}', [MedicinaGeneralController::class, 'destroyRevisionOrganos']);

    // Examen Fisico
    Route::get('/examen-fisico', [MedicinaGeneralController::class, 'indexExamenFisico']);
    Route::get('/examen-fisico/{id}', [MedicinaGeneralController::class, 'showExamenFisico']);
    Route::post('/examen-fisico', [MedicinaGeneralController::class, 'storeExamenFisico']);
    Route::put('/examen-fisico/{id}', [MedicinaGeneralController::class, 'updateExamenFisico']);
    Route::delete('/examen-fisico/{id}', [MedicinaGeneralController::class, 'destroyExamenFisico']);

    // Diagnosticos
    Route::get('/diagnosticos', [MedicinaGeneralController::class, 'indexDiagnosticos']);
    Route::get('/diagnosticos/{id}', [MedicinaGeneralController::class, 'showDiagnostico']);
    Route::post('/diagnosticos', [MedicinaGeneralController::class, 'storeDiagnostico']);
    Route::put('/diagnosticos/{id}', [MedicinaGeneralController::class, 'updateDiagnostico']);
    Route::delete('/diagnosticos/{id}', [MedicinaGeneralController::class, 'destroyDiagnostico']);

    // Planes Terapeuticos
    Route::get('/planes-terapeuticos', [MedicinaGeneralController::class, 'indexPlanesTerapeuticos']);
    Route::get('/planes-terapeuticos/{id}', [MedicinaGeneralController::class, 'showPlanTerapeutico']);
    Route::post('/planes-terapeuticos', [MedicinaGeneralController::class, 'storePlanTerapeutico']);
    Route::put('/planes-terapeuticos/{id}', [MedicinaGeneralController::class, 'updatePlanTerapeutico']);
    Route::delete('/planes-terapeuticos/{id}', [MedicinaGeneralController::class, 'destroyPlanTerapeutico']);

    // Historial Evolucion
    Route::get('/historial-evolucion', [MedicinaGeneralController::class, 'indexHistorialEvolucion']);
    Route::get('/historial-evolucion/{id}', [MedicinaGeneralController::class, 'showHistorialEvolucion']);
    Route::post('/historial-evolucion', [MedicinaGeneralController::class, 'storeHistorialEvolucion']);
    Route::put('/historial-evolucion/{id}', [MedicinaGeneralController::class, 'updateHistorialEvolucion']);
    Route::delete('/historial-evolucion/{id}', [MedicinaGeneralController::class, 'destroyHistorialEvolucion']);

    // Signos Vitales - Marcar como atendido y pacientes pendientes
    Route::patch('/signos-vitales/{id}/marcar-atendido', [MedicinaGeneralController::class, 'marcarAtendido']);
    Route::get('/signos-vitales/pacientes-pendientes', [MedicinaGeneralController::class, 'pacientesPendientes']);

    // Blood Type (medico general)
    Route::get('/patient/{id}/blood-type', [MedicinaGeneralController::class, 'getPatientBloodType']);
    Route::post('/patient/{id}/blood-type', [MedicinaGeneralController::class, 'storePatientBloodType']);
});

// Medicina Ocupacional
Route::prefix('v1/medicina-ocupacional')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    // Cargos
    Route::get('/cargos', [MedicinaOcupacionalController::class, 'indexCargos']);
    Route::get('/cargos/{id}', [MedicinaOcupacionalController::class, 'showCargo']);
    Route::post('/cargos', [MedicinaOcupacionalController::class, 'storeCargo']);
    Route::put('/cargos/{id}', [MedicinaOcupacionalController::class, 'updateCargo']);
    Route::delete('/cargos/{id}', [MedicinaOcupacionalController::class, 'destroyCargo']);

    // Usuario tiene cargo
    Route::get('/usuario-tiene-cargo', [MedicinaOcupacionalController::class, 'indexUsuarioTieneCargo']);
    Route::get('/usuario-tiene-cargo/{id}', [MedicinaOcupacionalController::class, 'showUsuarioTieneCargo']);
    Route::post('/usuario-tiene-cargo', [MedicinaOcupacionalController::class, 'storeUsuarioTieneCargo']);
    Route::put('/usuario-tiene-cargo/{id}', [MedicinaOcupacionalController::class, 'updateUsuarioTieneCargo']);
    Route::delete('/usuario-tiene-cargo/{id}', [MedicinaOcupacionalController::class, 'destroyUsuarioTieneCargo']);

    // Usuario lugar de trabajo
    Route::get('/usuario-lugar-de-trabajo', [MedicinaOcupacionalController::class, 'indexUsuarioLugarDeTrabajo']);
    Route::get('/usuario-lugar-de-trabajo/{id}', [MedicinaOcupacionalController::class, 'showUsuarioLugarDeTrabajo']);
    Route::post('/usuario-lugar-de-trabajo', [MedicinaOcupacionalController::class, 'storeUsuarioLugarDeTrabajo']);
    Route::put('/usuario-lugar-de-trabajo/{id}', [MedicinaOcupacionalController::class, 'updateUsuarioLugarDeTrabajo']);
    Route::delete('/usuario-lugar-de-trabajo/{id}', [MedicinaOcupacionalController::class, 'destroyUsuarioLugarDeTrabajo']);

    // Grupo tipo examen
    Route::get('/grupo-examen', [MedicinaOcupacionalController::class, 'indexGrupoExamen']);
    Route::get('/grupo-examen/{id}', [MedicinaOcupacionalController::class, 'showGrupoExamen']);
    Route::post('/grupo-examen', [MedicinaOcupacionalController::class, 'storeGrupoExamen']);
    Route::put('/grupo-examen/{id}', [MedicinaOcupacionalController::class, 'updateGrupoExamen']);
    Route::delete('/grupo-examen/{id}', [MedicinaOcupacionalController::class, 'destroyGrupoExamen']);

    // Tipo examen
    Route::get('/tipo-examen', [MedicinaOcupacionalController::class, 'indexTipoExamen']);
    Route::get('/tipo-examen/{id}', [MedicinaOcupacionalController::class, 'showTipoExamen']);
    Route::post('/tipo-examen', [MedicinaOcupacionalController::class, 'storeTipoExamen']);
    Route::put('/tipo-examen/{id}', [MedicinaOcupacionalController::class, 'updateTipoExamen']);
    Route::delete('/tipo-examen/{id}', [MedicinaOcupacionalController::class, 'destroyTipoExamen']);

    // Orden de examen
    Route::get('/orden-examen', [MedicinaOcupacionalController::class, 'indexOrdenExamen']);
    Route::get('/orden-examen/{id}', [MedicinaOcupacionalController::class, 'showOrdenExamen']);
    Route::post('/orden-examen', [MedicinaOcupacionalController::class, 'storeOrdenExamen']);
    Route::put('/orden-examen/{id}', [MedicinaOcupacionalController::class, 'updateOrdenExamen']);
    Route::delete('/orden-examen/{id}', [MedicinaOcupacionalController::class, 'destroyOrdenExamen']);

    // Orden examen tipo
    Route::get('/orden-examen-tipo', [MedicinaOcupacionalController::class, 'indexOrdenExamenTipo']);
    Route::post('/orden-examen-tipo', [MedicinaOcupacionalController::class, 'storeOrdenExamenTipo']);
    Route::delete('/orden-examen-tipo/{id}', [MedicinaOcupacionalController::class, 'destroyOrdenExamenTipo']);

    // Orden examen otros
    Route::get('/orden-examen-otros', [MedicinaOcupacionalController::class, 'indexOrdenExamenOtros']);
    Route::post('/orden-examen-otros', [MedicinaOcupacionalController::class, 'storeOrdenExamenOtros']);
    Route::put('/orden-examen-otros/{id}', [MedicinaOcupacionalController::class, 'updateOrdenExamenOtros']);
    Route::delete('/orden-examen-otros/{id}', [MedicinaOcupacionalController::class, 'destroyOrdenExamenOtros']);

    // Receta
    Route::get('/receta', [MedicinaOcupacionalController::class, 'indexReceta']);
    Route::get('/receta/{id}', [MedicinaOcupacionalController::class, 'showReceta']);
    Route::post('/receta', [MedicinaOcupacionalController::class, 'storeReceta']);
    Route::put('/receta/{id}', [MedicinaOcupacionalController::class, 'updateReceta']);
    Route::delete('/receta/{id}', [MedicinaOcupacionalController::class, 'destroyReceta']);

    // Receta CIE10
    Route::get('/receta-cie', [MedicinaOcupacionalController::class, 'indexRecetaCie']);
    Route::post('/receta-cie', [MedicinaOcupacionalController::class, 'storeRecetaCie']);
    Route::put('/receta-cie/{id}', [MedicinaOcupacionalController::class, 'updateRecetaCie']);
    Route::delete('/receta-cie/{id}', [MedicinaOcupacionalController::class, 'destroyRecetaCie']);

    // Linea receta
    Route::get('/linea-receta', [MedicinaOcupacionalController::class, 'indexLineaReceta']);
    Route::post('/linea-receta', [MedicinaOcupacionalController::class, 'storeLineaReceta']);
    Route::put('/linea-receta/{id}', [MedicinaOcupacionalController::class, 'updateLineaReceta']);
    Route::delete('/linea-receta/{id}', [MedicinaOcupacionalController::class, 'destroyLineaReceta']);

    // Signo alarma receta
    Route::get('/signo-alarma-receta', [MedicinaOcupacionalController::class, 'indexSignoAlarmaReceta']);
    Route::post('/signo-alarma-receta', [MedicinaOcupacionalController::class, 'storeSignoAlarmaReceta']);
    Route::delete('/signo-alarma-receta/{id}', [MedicinaOcupacionalController::class, 'destroySignoAlarmaReceta']);

    // Recomendaciones receta
    Route::get('/recomendacion-receta', [MedicinaOcupacionalController::class, 'indexRecomendacionReceta']);
    Route::post('/recomendacion-receta', [MedicinaOcupacionalController::class, 'storeRecomendacionReceta']);
    Route::put('/recomendacion-receta/{id}', [MedicinaOcupacionalController::class, 'updateRecomendacionReceta']);
    Route::delete('/recomendacion-receta/{id}', [MedicinaOcupacionalController::class, 'destroyRecomendacionReceta']);

    // Listado vacunas
    Route::get('/listado-vacunas', [MedicinaOcupacionalController::class, 'indexListadoVacunas']);
    Route::get('/listado-vacunas/{id}', [MedicinaOcupacionalController::class, 'showListadoVacunas']);
    Route::post('/listado-vacunas', [MedicinaOcupacionalController::class, 'storeListadoVacunas']);
    Route::put('/listado-vacunas/{id}', [MedicinaOcupacionalController::class, 'updateListadoVacunas']);
    Route::delete('/listado-vacunas/{id}', [MedicinaOcupacionalController::class, 'destroyListadoVacunas']);

    // Historial vacunas
    Route::get('/historial-vacunas', [MedicinaOcupacionalController::class, 'indexHistorialVacunas']);
    Route::get('/historial-vacunas/{id}', [MedicinaOcupacionalController::class, 'showHistorialVacunas']);
    Route::post('/historial-vacunas', [MedicinaOcupacionalController::class, 'storeHistorialVacunas']);
    Route::put('/historial-vacunas/{id}', [MedicinaOcupacionalController::class, 'updateHistorialVacunas']);
    Route::delete('/historial-vacunas/{id}', [MedicinaOcupacionalController::class, 'destroyHistorialVacunas']);

    // Reintegro UEB
    Route::get('/reintegro-ueb', [MedicinaOcupacionalController::class, 'indexReintegroUeb']);
    Route::get('/reintegro-ueb/{id}', [MedicinaOcupacionalController::class, 'showReintegroUeb']);
    Route::post('/reintegro-ueb', [MedicinaOcupacionalController::class, 'storeReintegroUeb']);
    Route::put('/reintegro-ueb/{id}', [MedicinaOcupacionalController::class, 'updateReintegroUeb']);
    Route::delete('/reintegro-ueb/{id}', [MedicinaOcupacionalController::class, 'destroyReintegroUeb']);

    // Personal nuevo
    Route::get('/personal-nuevo', [MedicinaOcupacionalController::class, 'indexPersonalNuevo']);
    Route::get('/personal-nuevo/{id}', [MedicinaOcupacionalController::class, 'showPersonalNuevo']);
    Route::post('/personal-nuevo', [MedicinaOcupacionalController::class, 'storePersonalNuevo']);
    Route::put('/personal-nuevo/{id}', [MedicinaOcupacionalController::class, 'updatePersonalNuevo']);
    Route::delete('/personal-nuevo/{id}', [MedicinaOcupacionalController::class, 'destroyPersonalNuevo']);

    // Cese de funciones
    Route::get('/cese-funciones', [MedicinaOcupacionalController::class, 'indexCeseFunciones']);
    Route::get('/cese-funciones/{id}', [MedicinaOcupacionalController::class, 'showCeseFunciones']);
    Route::post('/cese-funciones', [MedicinaOcupacionalController::class, 'storeCeseFunciones']);
    Route::put('/cese-funciones/{id}', [MedicinaOcupacionalController::class, 'updateCeseFunciones']);
    Route::delete('/cese-funciones/{id}', [MedicinaOcupacionalController::class, 'destroyCeseFunciones']);

    // Lista vulnerabilidades
    Route::get('/lista-vulnerabilidades', [MedicinaOcupacionalController::class, 'indexListaVulnerabilidades']);
    Route::get('/lista-vulnerabilidades/{id}', [MedicinaOcupacionalController::class, 'showListaVulnerabilidades']);
    Route::post('/lista-vulnerabilidades', [MedicinaOcupacionalController::class, 'storeListaVulnerabilidades']);
    Route::put('/lista-vulnerabilidades/{id}', [MedicinaOcupacionalController::class, 'updateListaVulnerabilidades']);
    Route::delete('/lista-vulnerabilidades/{id}', [MedicinaOcupacionalController::class, 'destroyListaVulnerabilidades']);

    // Grupo vulnerable
    Route::get('/grupo-vulnerable', [MedicinaOcupacionalController::class, 'indexGrupoVulnerable']);
    Route::get('/grupo-vulnerable/{id}', [MedicinaOcupacionalController::class, 'showGrupoVulnerable']);
    Route::post('/grupo-vulnerable', [MedicinaOcupacionalController::class, 'storeGrupoVulnerable']);
    Route::put('/grupo-vulnerable/{id}', [MedicinaOcupacionalController::class, 'updateGrupoVulnerable']);
    Route::delete('/grupo-vulnerable/{id}', [MedicinaOcupacionalController::class, 'destroyGrupoVulnerable']);

    // Grupo riesgo psicosocial
    Route::get('/grupo-riesgo-psicosocial', [MedicinaOcupacionalController::class, 'indexGrupoRiesgoPsicosocial']);
    Route::get('/grupo-riesgo-psicosocial/{id}', [MedicinaOcupacionalController::class, 'showGrupoRiesgoPsicosocial']);
    Route::post('/grupo-riesgo-psicosocial', [MedicinaOcupacionalController::class, 'storeGrupoRiesgoPsicosocial']);
    Route::put('/grupo-riesgo-psicosocial/{id}', [MedicinaOcupacionalController::class, 'updateGrupoRiesgoPsicosocial']);
    Route::delete('/grupo-riesgo-psicosocial/{id}', [MedicinaOcupacionalController::class, 'destroyGrupoRiesgoPsicosocial']);

    // Listado discapacidades
    Route::get('/listado-discapacidades', [MedicinaOcupacionalController::class, 'indexListadoDiscapacidades']);
    Route::get('/listado-discapacidades/{id}', [MedicinaOcupacionalController::class, 'showListadoDiscapacidades']);
    Route::post('/listado-discapacidades', [MedicinaOcupacionalController::class, 'storeListadoDiscapacidades']);
    Route::put('/listado-discapacidades/{id}', [MedicinaOcupacionalController::class, 'updateListadoDiscapacidades']);
    Route::delete('/listado-discapacidades/{id}', [MedicinaOcupacionalController::class, 'destroyListadoDiscapacidades']);

    // Funcionarios discapacidad
    Route::get('/funcionarios-discapacidad', [MedicinaOcupacionalController::class, 'indexFuncionariosDiscapacidad']);
    Route::get('/funcionarios-discapacidad/{id}', [MedicinaOcupacionalController::class, 'showFuncionariosDiscapacidad']);
    Route::post('/funcionarios-discapacidad', [MedicinaOcupacionalController::class, 'storeFuncionariosDiscapacidad']);
    Route::put('/funcionarios-discapacidad/{id}', [MedicinaOcupacionalController::class, 'updateFuncionariosDiscapacidad']);
    Route::delete('/funcionarios-discapacidad/{id}', [MedicinaOcupacionalController::class, 'destroyFuncionariosDiscapacidad']);

    // Enfermedades nuevas
    Route::get('/enfermedades-nuevas', [MedicinaOcupacionalController::class, 'indexEnfermedadesNuevas']);
    Route::get('/enfermedades-nuevas/{id}', [MedicinaOcupacionalController::class, 'showEnfermedadesNuevas']);
    Route::post('/enfermedades-nuevas', [MedicinaOcupacionalController::class, 'storeEnfermedadesNuevas']);
    Route::put('/enfermedades-nuevas/{id}', [MedicinaOcupacionalController::class, 'updateEnfermedadesNuevas']);
    Route::delete('/enfermedades-nuevas/{id}', [MedicinaOcupacionalController::class, 'destroyEnfermedadesNuevas']);

    // Enfermedades catastroficas
    Route::get('/enfermedades-catastroficas', [MedicinaOcupacionalController::class, 'indexEnfermedadesCatastroficas']);
    Route::get('/enfermedades-catastroficas/{id}', [MedicinaOcupacionalController::class, 'showEnfermedadesCatastroficas']);
    Route::post('/enfermedades-catastroficas', [MedicinaOcupacionalController::class, 'storeEnfermedadesCatastroficas']);
    Route::put('/enfermedades-catastroficas/{id}', [MedicinaOcupacionalController::class, 'updateEnfermedadesCatastroficas']);
    Route::delete('/enfermedades-catastroficas/{id}', [MedicinaOcupacionalController::class, 'destroyEnfermedadesCatastroficas']);

    // Listado embarazadas
    Route::get('/listado-embarazadas', [MedicinaOcupacionalController::class, 'indexListadoEmbarazadas']);
    Route::get('/listado-embarazadas/{id}', [MedicinaOcupacionalController::class, 'showListadoEmbarazadas']);
    Route::post('/listado-embarazadas', [MedicinaOcupacionalController::class, 'storeListadoEmbarazadas']);
    Route::put('/listado-embarazadas/{id}', [MedicinaOcupacionalController::class, 'updateListadoEmbarazadas']);
    Route::delete('/listado-embarazadas/{id}', [MedicinaOcupacionalController::class, 'destroyListadoEmbarazadas']);

    // Influenza
    Route::get('/influenza', [MedicinaOcupacionalController::class, 'indexInfluenza']);
    Route::get('/influenza/{id}', [MedicinaOcupacionalController::class, 'showInfluenza']);
    Route::post('/influenza', [MedicinaOcupacionalController::class, 'storeInfluenza']);
    Route::put('/influenza/{id}', [MedicinaOcupacionalController::class, 'updateInfluenza']);
    Route::delete('/influenza/{id}', [MedicinaOcupacionalController::class, 'destroyInfluenza']);

    // Ausentismo laboral
    Route::get('/ausentismo-laboral', [MedicinaOcupacionalController::class, 'indexAusentismoLaboral']);
    Route::get('/ausentismo-laboral/{id}', [MedicinaOcupacionalController::class, 'showAusentismoLaboral']);
    Route::post('/ausentismo-laboral', [MedicinaOcupacionalController::class, 'storeAusentismoLaboral']);
    Route::put('/ausentismo-laboral/{id}', [MedicinaOcupacionalController::class, 'updateAusentismoLaboral']);
    Route::delete('/ausentismo-laboral/{id}', [MedicinaOcupacionalController::class, 'destroyAusentismoLaboral']);

    // Accidentes laborales
    Route::get('/accidentes-laborales', [MedicinaOcupacionalController::class, 'indexAccidentesLaborales']);
    Route::get('/accidentes-laborales/{id}', [MedicinaOcupacionalController::class, 'showAccidentesLaborales']);
    Route::post('/accidentes-laborales', [MedicinaOcupacionalController::class, 'storeAccidentesLaborales']);
    Route::put('/accidentes-laborales/{id}', [MedicinaOcupacionalController::class, 'updateAccidentesLaborales']);
    Route::delete('/accidentes-laborales/{id}', [MedicinaOcupacionalController::class, 'destroyAccidentesLaborales']);

    // Blood Type (medico ocupacional)
    Route::get('/patient/{id}/blood-type', [MedicinaOcupacionalController::class, 'getPatientBloodType']);
    Route::post('/patient/{id}/blood-type', [MedicinaOcupacionalController::class, 'storePatientBloodType']);
});

// Psicologia
Route::prefix('v1/psicologia')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    // Motivo Consulta
    Route::get('/motivo-consulta', [PsicologiaController::class, 'indexMotivoConsulta']);
    Route::get('/motivo-consulta/{id}', [PsicologiaController::class, 'showMotivoConsulta']);
    Route::post('/motivo-consulta', [PsicologiaController::class, 'storeMotivoConsulta']);
    Route::put('/motivo-consulta/{id}', [PsicologiaController::class, 'updateMotivoConsulta']);
    Route::delete('/motivo-consulta/{id}', [PsicologiaController::class, 'destroyMotivoConsulta']);

    // Psicoanamnesis
    Route::get('/psicoanamnesis', [PsicologiaController::class, 'indexPsicoanamnesis']);
    Route::get('/psicoanamnesis/{id}', [PsicologiaController::class, 'showPsicoanamnesis']);
    Route::post('/psicoanamnesis', [PsicologiaController::class, 'storePsicoanamnesis']);
    Route::put('/psicoanamnesis/{id}', [PsicologiaController::class, 'updatePsicoanamnesis']);
    Route::delete('/psicoanamnesis/{id}', [PsicologiaController::class, 'destroyPsicoanamnesis']);

    // Historial Laboral
    Route::get('/historial-laboral', [PsicologiaController::class, 'indexHistorialLaboral']);
    Route::get('/historial-laboral/{id}', [PsicologiaController::class, 'showHistorialLaboral']);
    Route::post('/historial-laboral', [PsicologiaController::class, 'storeHistorialLaboral']);
    Route::put('/historial-laboral/{id}', [PsicologiaController::class, 'updateHistorialLaboral']);
    Route::delete('/historial-laboral/{id}', [PsicologiaController::class, 'destroyHistorialLaboral']);

    // Historial Social
    Route::get('/historial-social', [PsicologiaController::class, 'indexHistorialSocial']);
    Route::get('/historial-social/{id}', [PsicologiaController::class, 'showHistorialSocial']);
    Route::post('/historial-social', [PsicologiaController::class, 'storeHistorialSocial']);
    Route::put('/historial-social/{id}', [PsicologiaController::class, 'updateHistorialSocial']);
    Route::delete('/historial-social/{id}', [PsicologiaController::class, 'destroyHistorialSocial']);

    // Historial Sexual
    Route::get('/historial-sexual', [PsicologiaController::class, 'indexHistorialSexual']);
    Route::get('/historial-sexual/{id}', [PsicologiaController::class, 'showHistorialSexual']);
    Route::post('/historial-sexual', [PsicologiaController::class, 'storeHistorialSexual']);
    Route::put('/historial-sexual/{id}', [PsicologiaController::class, 'updateHistorialSexual']);
    Route::delete('/historial-sexual/{id}', [PsicologiaController::class, 'destroyHistorialSexual']);

    // Patologias
    Route::get('/patologias', [PsicologiaController::class, 'indexPatologias']);
    Route::get('/patologias/{id}', [PsicologiaController::class, 'showPatologias']);
    Route::post('/patologias', [PsicologiaController::class, 'storePatologias']);
    Route::put('/patologias/{id}', [PsicologiaController::class, 'updatePatologias']);
    Route::delete('/patologias/{id}', [PsicologiaController::class, 'destroyPatologias']);

    // Examen Estado Mental
    Route::get('/examen-estado-mental', [PsicologiaController::class, 'indexExamenEstadoMental']);
    Route::get('/examen-estado-mental/{id}', [PsicologiaController::class, 'showExamenEstadoMental']);
    Route::post('/examen-estado-mental', [PsicologiaController::class, 'storeExamenEstadoMental']);
    Route::put('/examen-estado-mental/{id}', [PsicologiaController::class, 'updateExamenEstadoMental']);
    Route::delete('/examen-estado-mental/{id}', [PsicologiaController::class, 'destroyExamenEstadoMental']);

    // Pruebas Aplicadas
    Route::get('/pruebas-aplicadas', [PsicologiaController::class, 'indexPruebasAplicadas']);
    Route::get('/pruebas-aplicadas/{id}', [PsicologiaController::class, 'showPruebasAplicadas']);
    Route::post('/pruebas-aplicadas', [PsicologiaController::class, 'storePruebasAplicadas']);
    Route::put('/pruebas-aplicadas/{id}', [PsicologiaController::class, 'updatePruebasAplicadas']);
    Route::delete('/pruebas-aplicadas/{id}', [PsicologiaController::class, 'destroyPruebasAplicadas']);

    // Analisis Resultados
    Route::get('/analisis-resultados', [PsicologiaController::class, 'indexAnalisisResultados']);
    Route::get('/analisis-resultados/{id}', [PsicologiaController::class, 'showAnalisisResultados']);
    Route::post('/analisis-resultados', [PsicologiaController::class, 'storeAnalisisResultados']);
    Route::put('/analisis-resultados/{id}', [PsicologiaController::class, 'updateAnalisisResultados']);
    Route::delete('/analisis-resultados/{id}', [PsicologiaController::class, 'destroyAnalisisResultados']);

    // Conclusiones
    Route::get('/conclusiones', [PsicologiaController::class, 'indexConclusiones']);
    Route::get('/conclusiones/{id}', [PsicologiaController::class, 'showConclusiones']);
    Route::post('/conclusiones', [PsicologiaController::class, 'storeConclusiones']);
    Route::put('/conclusiones/{id}', [PsicologiaController::class, 'updateConclusiones']);
    Route::delete('/conclusiones/{id}', [PsicologiaController::class, 'destroyConclusiones']);

    // Diagnostico
    Route::get('/diagnostico', [PsicologiaController::class, 'indexDiagnostico']);
    Route::get('/diagnostico/{id}', [PsicologiaController::class, 'showDiagnostico']);
    Route::post('/diagnostico', [PsicologiaController::class, 'storeDiagnostico']);
    Route::put('/diagnostico/{id}', [PsicologiaController::class, 'updateDiagnostico']);
    Route::delete('/diagnostico/{id}', [PsicologiaController::class, 'destroyDiagnostico']);

    // Pronostico
    Route::get('/pronostico', [PsicologiaController::class, 'indexPronostico']);
    Route::get('/pronostico/{id}', [PsicologiaController::class, 'showPronostico']);
    Route::post('/pronostico', [PsicologiaController::class, 'storePronostico']);
    Route::put('/pronostico/{id}', [PsicologiaController::class, 'updatePronostico']);
    Route::delete('/pronostico/{id}', [PsicologiaController::class, 'destroyPronostico']);

    // Recomendacion
    Route::get('/recomendacion', [PsicologiaController::class, 'indexRecomendacion']);
    Route::get('/recomendacion/{id}', [PsicologiaController::class, 'showRecomendacion']);
    Route::post('/recomendacion', [PsicologiaController::class, 'storeRecomendacion']);
    Route::put('/recomendacion/{id}', [PsicologiaController::class, 'updateRecomendacion']);
    Route::delete('/recomendacion/{id}', [PsicologiaController::class, 'destroyRecomendacion']);

    // Historial Evolucion (Sesiones)
    Route::get('/historial-evolucion', [PsicologiaController::class, 'indexHistorialEvolucion']);
    Route::get('/historial-evolucion/{id}', [PsicologiaController::class, 'showHistorialEvolucion']);
    Route::post('/historial-evolucion', [PsicologiaController::class, 'storeHistorialEvolucion']);
    Route::put('/historial-evolucion/{id}', [PsicologiaController::class, 'updateHistorialEvolucion']);
    Route::delete('/historial-evolucion/{id}', [PsicologiaController::class, 'destroyHistorialEvolucion']);

    // Parte Diario
    Route::get('/parte-diario', [PsicologiaController::class, 'indexParteDiario']);
    Route::get('/parte-diario/{id}', [PsicologiaController::class, 'showParteDiario']);
    Route::post('/parte-diario', [PsicologiaController::class, 'storeParteDiario']);
    Route::put('/parte-diario/{id}', [PsicologiaController::class, 'updateParteDiario']);
    Route::delete('/parte-diario/{id}', [PsicologiaController::class, 'destroyParteDiario']);
});

// Reportes
Route::prefix('v1/reportes')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    Route::get('/', [ReportesController::class, 'reporte']);
    Route::get('/pdf', [ReportesController::class, 'reportePdf']);
    Route::get('/catalogos', [ReportesController::class, 'catalogos']);
});

// Odontologia
Route::prefix('v1/odontologia')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    // Procedimientos
    Route::get('/procedimientos', [OdontologiaController::class, 'indexProcedimientos']);
    Route::post('/procedimientos', [OdontologiaController::class, 'storeProcedimiento']);
    Route::delete('/procedimientos/{id}', [OdontologiaController::class, 'destroyProcedimiento']);

    // Motivo Consulta
    Route::get('/motivo-consulta', [OdontologiaController::class, 'indexMotivoConsulta']);
    Route::get('/motivo-consulta/{id}', [OdontologiaController::class, 'showMotivoConsulta']);
    Route::post('/motivo-consulta', [OdontologiaController::class, 'storeMotivoConsulta']);
    Route::put('/motivo-consulta/{id}', [OdontologiaController::class, 'updateMotivoConsulta']);
    Route::delete('/motivo-consulta/{id}', [OdontologiaController::class, 'destroyMotivoConsulta']);

    // Examen Odontologia
    Route::get('/examen', [OdontologiaController::class, 'indexExamen']);
    Route::get('/examen/{id}', [OdontologiaController::class, 'showExamen']);
    Route::post('/examen', [OdontologiaController::class, 'storeExamen']);
    Route::put('/examen/{id}', [OdontologiaController::class, 'updateExamen']);
    Route::delete('/examen/{id}', [OdontologiaController::class, 'destroyExamen']);

    // Enfermedad Periodontal
    Route::get('/enfermedad-periodontal', [OdontologiaController::class, 'indexEnfermedadPeriodontal']);
    Route::get('/enfermedad-periodontal/{id}', [OdontologiaController::class, 'showEnfermedadPeriodontal']);
    Route::post('/enfermedad-periodontal', [OdontologiaController::class, 'storeEnfermedadPeriodontal']);
    Route::put('/enfermedad-periodontal/{id}', [OdontologiaController::class, 'updateEnfermedadPeriodontal']);
    Route::delete('/enfermedad-periodontal/{id}', [OdontologiaController::class, 'destroyEnfermedadPeriodontal']);

    // Historial Evolucion
    Route::get('/historial-evolucion', [OdontologiaController::class, 'indexHistorialEvolucion']);
    Route::get('/historial-evolucion/{id}', [OdontologiaController::class, 'showHistorialEvolucion']);
    Route::post('/historial-evolucion', [OdontologiaController::class, 'storeHistorialEvolucion']);
    Route::put('/historial-evolucion/{id}', [OdontologiaController::class, 'updateHistorialEvolucion']);
    Route::delete('/historial-evolucion/{id}', [OdontologiaController::class, 'destroyHistorialEvolucion']);

    // Catalogo Insumos Odontologia
    Route::get('/catalogo-insumos', [OdontologiaController::class, 'indexCatalogoInsumos']);
    Route::get('/catalogo-insumos/{id}', [OdontologiaController::class, 'showCatalogoInsumos']);
    Route::post('/catalogo-insumos', [OdontologiaController::class, 'storeCatalogoInsumos']);
    Route::put('/catalogo-insumos/{id}', [OdontologiaController::class, 'updateCatalogoInsumos']);
    Route::delete('/catalogo-insumos/{id}', [OdontologiaController::class, 'destroyCatalogoInsumos']);

    // Parte Diario Odontologia
    Route::get('/parte-diario-odontologia', [OdontologiaController::class, 'indexParteDiario']);
    Route::get('/parte-diario-odontologia/{id}', [OdontologiaController::class, 'showParteDiario']);
    Route::post('/parte-diario-odontologia', [OdontologiaController::class, 'storeParteDiario']);
    Route::put('/parte-diario-odontologia/{id}', [OdontologiaController::class, 'updateParteDiario']);
    Route::delete('/parte-diario-odontologia/{id}', [OdontologiaController::class, 'destroyParteDiario']);

    // Insumos Paciente Odontologia
    Route::get('/insumos-paciente', [OdontologiaController::class, 'indexInsumosPaciente']);
    Route::get('/insumos-paciente/{id}', [OdontologiaController::class, 'showInsumosPaciente']);
    Route::post('/insumos-paciente', [OdontologiaController::class, 'storeInsumosPaciente']);
    Route::put('/insumos-paciente/{id}', [OdontologiaController::class, 'updateInsumosPaciente']);
    Route::delete('/insumos-paciente/{id}', [OdontologiaController::class, 'destroyInsumosPaciente']);

    // Odontograma Estados
    Route::get('/odontograma-estados', [OdontologiaController::class, 'indexEstadoOdontograma']);
    Route::get('/odontograma-estados/{id}', [OdontologiaController::class, 'showEstadoOdontograma']);
    Route::post('/odontograma-estados', [OdontologiaController::class, 'storeEstadoOdontograma']);
    Route::put('/odontograma-estados/{id}', [OdontologiaController::class, 'updateEstadoOdontograma']);
    Route::delete('/odontograma-estados/{id}', [OdontologiaController::class, 'destroyEstadoOdontograma']);

    // Odontograma Paciente
    Route::get('/odontograma-paciente/{pacienteId}', [OdontologiaController::class, 'showOdontogramaPaciente']);
    Route::post('/odontograma-paciente', [OdontologiaController::class, 'storeOdontogramaPaciente']);

    // Odontograma Asignaciones
    Route::get('/odontograma-asignaciones', [OdontologiaController::class, 'indexAsignacionOdontograma']);
    Route::post('/odontograma-asignaciones', [OdontologiaController::class, 'storeAsignacionOdontograma']);
    Route::put('/odontograma-asignaciones/{id}', [OdontologiaController::class, 'updateAsignacionOdontograma']);
    Route::delete('/odontograma-asignaciones/{id}', [OdontologiaController::class, 'destroyAsignacionOdontograma']);
});

// Citas Medicas
Route::prefix('v1/citas-medicas')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    // Doctor - Citas asignadas
    Route::get('/doctor/citas', [CitasMedicasController::class, 'citasDoctor']);
    Route::get('/doctor/calendario', [CitasMedicasController::class, 'calendarioDoctor']);

    // Disponibilidad y doctores
    Route::get('/disponibilidad', [CitasMedicasController::class, 'disponibilidad']);
    Route::get('/verificar-acceso', [CitasMedicasController::class, 'verificarAcceso']);
    Route::get('/doctores-por-rol', [CitasMedicasController::class, 'doctoresPorRol']);
    Route::get('/doctores-disponibles', [CitasMedicasController::class, 'doctoresDisponibles']);

    // Paciente - Mis citas
    Route::get('/mis-citas', [CitasMedicasController::class, 'misCitas']);
    Route::post('/', [CitasMedicasController::class, 'store']);
    Route::post('/atender-paciente', [CitasMedicasController::class, 'registrarAtencionAutoCita']);

    // Wildcard routes last to avoid matching static prefixes
    Route::get('/{id}', [CitasMedicasController::class, 'show']);
    Route::patch('/{id}/cancelar', [CitasMedicasController::class, 'cancelar']);
    Route::patch('/{id}/confirmar-asistencia', [CitasMedicasController::class, 'confirmarAsistencia']);
    Route::patch('/{id}/confirmar', [CitasMedicasController::class, 'confirmar']);
    Route::patch('/{id}/completar', [CitasMedicasController::class, 'completar']);
});

// ==========================================
// FARMACIA
// ==========================================

// Presentaciones (Solo Enfermera, Admin, Coordinador CRUD)
Route::prefix('v1/presentaciones')->middleware(['auth:sanctum', 'force_password_change', 'role:enfermero|administrador|medico_coordinador|medico_general|medico_ocupacional|odontologo|psicologo'])->group(function () {
    Route::get('/', [PresentacionController::class, 'index']);
    Route::get('/{id}', [PresentacionController::class, 'show']);
    Route::post('/', [PresentacionController::class, 'store']);
    Route::put('/{id}', [PresentacionController::class, 'update']);
    Route::delete('/{id}', [PresentacionController::class, 'destroy']);
});

// Productos de Farmacia (Solo Enfermera, Admin, Coordinador CRUD + Inventario)
Route::prefix('v1/farmacia')->middleware(['auth:sanctum', 'force_password_change', 'role:enfermero|administrador|medico_coordinador|medico_general|medico_ocupacional|odontologo|psicologo'])->group(function () {
    // CRUD Productos
    Route::get('/', [FarmaciaController::class, 'index']);
    Route::get('/{id}', [FarmaciaController::class, 'show']);
    Route::post('/', [FarmaciaController::class, 'store']);
    Route::put('/{id}', [FarmaciaController::class, 'update']);
    Route::patch('/{id}/disable', [FarmaciaController::class, 'disable']);
    Route::patch('/{id}/enable', [FarmaciaController::class, 'enable']);

    // Gestión de Stock (sumativo)
    Route::post('/{id}/stock/add', [FarmaciaController::class, 'addStock']);
    Route::post('/{id}/stock/subtract', [FarmaciaController::class, 'subtractStock']);

    // Historial de movimientos
    Route::get('/{id}/history', [FarmaciaController::class, 'stockHistory']);

    // Alertas
    Route::get('/low-stock/alert', [FarmaciaController::class, 'lowStock']);

    // Inventario completo
    Route::get('/inventory/report', [FarmaciaController::class, 'inventoryReport']);
});

// Búsqueda de productos (Médicos y Enfermera)
Route::prefix('v1/farmacia-search')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    Route::get('/', [FarmaciaController::class, 'search']);
    Route::get('/check-stock', [FarmaciaController::class, 'checkStock']);
});

// Integración Receta-Farmacia
Route::prefix('v1/receta-farmacia')->middleware(['auth:sanctum', 'force_password_change'])->group(function () {
    // Médicos: agregar producto a receta
    Route::middleware(['role:medico_ocupacional|medico_general|psicologo|odontologo|medico_coordinador|administrador'])->group(function () {
        Route::post('/linea-receta/{lineaRecetaId}/producto', [MedicinaOcupacionalController::class, 'agregarProductoReceta']);
        Route::get('/paciente/{pacienteId}', [MedicinaOcupacionalController::class, 'recetasPorPaciente']);
    });

    // Enfermera: despachar y ver pendientes
    Route::middleware(['role:enfermero|medico_ocupacional|medico_general|psicologo|odontologo|medico_coordinador|administrador'])->group(function () {
        Route::post('/linea-receta/{lineaRecetaId}/despachar', [MedicinaOcupacionalController::class, 'despacharProducto']);
        Route::get('/pendientes-despacho', [MedicinaOcupacionalController::class, 'recetasPendientesDespacho']);
    });
});