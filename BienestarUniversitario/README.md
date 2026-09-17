# Backend-Bienestar Universitario

API REST para el sistema de bienestar universitario que gestiona servicios de salud para estudiantes y personal.

## Stack Tecnológico

- **Framework:** Laravel 11
- **Autenticación:** Laravel Sanctum
- **Base de datos:** MySQL
- **PHP:** 8.x

## Requisitos

- PHP 8.1+
- Composer
- MySQL 8.0+
- Laravel Sanctum

## Instalación

```bash
# Clonar el repositorio
git clone <repo-url>
cd backend-bienestar

# Instalar dependencias
composer install

# Copiar configuración
cp .env.example .env

# Configurar base de datos en .env
DB_DATABASE=backend_bienestar
DB_USERNAME=root
DB_PASSWORD=

# Generar clave
php artisan key:generate

# Generar Sanctum
php artisan sanctum:install

# Ejecutar migraciones
php artisan migrate

# Poblar base de datos (usuarios de prueba)
php artisan db:seed
```

## Usuarios de Prueba

| Rol | Email | Password |
|-----|-------|----------|
| Administrador | admin@test.com | password123 |
| Enfermero | enfermero@test.com | password123 |
| Médico General | medico@test.com | password123 |
| Odontólogo | odontologo@test.com | password123 |
| Psicólogo | psicologo@test.com | password123 |
| Médico Ocupacional | medico_ocupacional@test.com | password123 |
| Paciente | paciente@test.com | password123 |

## Autenticación

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "odontologo@test.com",
  "password": "password123"
}
```

**Respuesta:**
```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

### Registro (público)
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "Nombre Usuario",
  "email": "email@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Logout
```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

### Cambiar Contraseña
```http
PUT /api/v1/auth/password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "password123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

## Endpoints de la API

**Base URL:** `/api/v1`

**Headers requeridos para endpoints protegidos:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

### Módulo: Auth

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/auth/login` | Iniciar sesión |
| POST | `/auth/register` | Registrar usuario |
| POST | `/auth/forgot-password` | Solicitar reset de contraseña |
| POST | `/auth/reset-password` | Resetear contraseña |
| POST | `/auth/logout` | Cerrar sesión |
| PUT | `/auth/password` | Cambiar contraseña |

---

### Módulo: Users

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/users` | Listar usuarios |
| POST | `/users` | Crear usuario |
| GET | `/users/search-by-cedula` | Buscar por cédula |
| GET | `/users/{id}` | Ver usuario |
| PUT | `/users/{id}` | Actualizar usuario |
| PUT | `/users/{id}/disable` | Deshabilitar usuario |
| PUT | `/users/{id}/enable` | Habilitar usuario |
| POST | `/users/{id}/reset-password` | Resetear contraseña |
| DELETE | `/users/{id}/roles/{role}` | Remover rol |

**Filtros:**
- `GET /users?role=odontologo` - Filtrar por rol

---

### Módulo: Roles

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/roles` | Listar roles |

---

### Módulo: User Profile

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/user-profile/profile` | Obtener perfil |
| GET | `/user-profile/identification` | Ver identificación |
| POST | `/user-profile/identification` | Crear identificación |
| PUT | `/user-profile/identification` | Actualizar identificación |
| GET | `/user-profile/children` | Listar hijos |
| POST | `/user-profile/children` | Agregar hijo |
| DELETE | `/user-profile/children/{id}` | Eliminar hijo |
| GET | `/user-profile/allergies` | Listar alergias |
| POST | `/user-profile/allergies` | Agregar alergia |
| DELETE | `/user-profile/allergies/{id}` | Eliminar alergia |
| GET | `/user-profile/disabilities` | Listar discapacidades |
| POST | `/user-profile/disabilities` | Agregar discapacidad |
| DELETE | `/user-profile/disabilities/{id}` | Eliminar discapacidad |
| GET | `/user-profile/career-study` | Ver carrera/estudio |
| POST | `/user-profile/career-study` | Crear carrera/estudio |
| GET | `/user-profile/demographic` | Ver datos demográficos |
| POST | `/user-profile/demographic` | Crear datos demográficos |
| GET | `/user-profile/addresses` | Listar direcciones |
| POST | `/user-profile/addresses` | Crear dirección |
| PUT | `/user-profile/addresses/{id}` | Actualizar dirección |
| DELETE | `/user-profile/addresses/{id}` | Eliminar dirección |
| GET | `/user-profile/emergency-contacts` | Listar contactos de emergencia |
| POST | `/user-profile/emergency-contacts` | Crear contacto |
| PUT | `/user-profile/emergency-contacts/{id}` | Actualizar contacto |
| DELETE | `/user-profile/emergency-contacts/{id}` | Eliminar contacto |
| GET | `/user-profile/photo` | Ver foto |
| POST | `/user-profile/photo` | Subir foto |

---

### Módulo: Medical Staff

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/medical-staff/cargos` | Listar cargos |
| POST | `/medical-staff/cargos` | Crear cargo |
| GET | `/medical-staff/mis-pacientes` | Mis pacientes asignados |
| POST | `/medical-staff/asignar-paciente` | Asignar paciente |
| DELETE | `/medical-staff/desasignar-paciente/{id}` | Desasignar paciente |
| GET | `/medical-staff/doctores` | Listar doctores |

---

### Módulo: Enfermería

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/enfermeria/procedimientos` | Listar procedimientos |
| GET | `/enfermeria/procedimientos/{id}` | Ver procedimiento |
| POST | `/enfermeria/procedimientos` | Crear procedimiento |
| PUT | `/enfermeria/procedimientos/{id}` | Actualizar procedimiento |
| DELETE | `/enfermeria/procedimientos/{id}` | Eliminar procedimiento |
| GET | `/enfermeria/signos-vitales` | Listar signos vitales |
| GET | `/enfermeria/signos-vitales/{id}` | Ver signos vitales |
| POST | `/enfermeria/signos-vitales` | Crear signos vitales |
| PUT | `/enfermeria/signos-vitales/{id}` | Actualizar signos vitales |
| DELETE | `/enfermeria/signos-vitales/{id}` | Eliminar signos vitales |
| GET | `/enfermeria/parte-diario` | Listar parte diario |
| GET | `/enfermeria/parte-diario/{id}` | Ver parte diario |
| POST | `/enfermeria/parte-diario` | Crear parte diario |
| PUT | `/enfermeria/parte-diario/{id}` | Actualizar parte diario |
| DELETE | `/enfermeria/parte-diario/{id}` | Eliminar parte diario |

---

### Módulo: Medicina General

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/medicina-general/signos-vitales` | Listar signos vitales |
| GET | `/medicina-general/signos-vitales/{id}` | Ver signos vitales |
| POST | `/medicina-general/signos-vitales` | Crear signos vitales |
| PUT | `/medicina-general/signos-vitales/{id}` | Actualizar signos vitales |
| DELETE | `/medicina-general/signos-vitales/{id}` | Eliminar signos vitales |
| GET | `/medicina-general/parte-diario` | Listar parte diario |
| GET | `/medicina-general/parte-diario/{id}` | Ver parte diario |
| POST | `/medicina-general/parte-diario` | Crear parte diario |
| PUT | `/medicina-general/parte-diario/{id}` | Actualizar parte diario |
| DELETE | `/medicina-general/parte-diario/{id}` | Eliminar parte diario |
| GET | `/medicina-general/motivo-consulta` | Listar motivos de consulta |
| GET | `/medicina-general/motivo-consulta/{id}` | Ver motivo consulta |
| POST | `/medicina-general/motivo-consulta` | Crear motivo consulta |
| PUT | `/medicina-general/motivo-consulta/{id}` | Actualizar motivo consulta |
| DELETE | `/medicina-general/motivo-consulta/{id}` | Eliminar motivo consulta |
| GET | `/medicina-general/antecedentes` | Listar antecedentes |
| GET | `/medicina-general/antecedentes/{id}` | Ver antecedente |
| POST | `/medicina-general/antecedentes` | Crear antecedente |
| PUT | `/medicina-general/antecedentes/{id}` | Actualizar antecedente |
| DELETE | `/medicina-general/antecedentes/{id}` | Eliminar antecedente |
| GET | `/medicina-general/enfermedades-actuales` | Listar enfermedades actuales |
| GET | `/medicina-general/enfermedades-actuales/{id}` | Ver enfermedad actual |
| POST | `/medicina-general/enfermedades-actuales` | Crear enfermedad actual |
| PUT | `/medicina-general/enfermedades-actuales/{id}` | Actualizar enfermedad actual |
| DELETE | `/medicina-general/enfermedades-actuales/{id}` | Eliminar enfermedad actual |
| GET | `/medicina-general/revision-organos` | Listar revisión por órganos |
| GET | `/medicina-general/revision-organos/{id}` | Ver revisión órganos |
| POST | `/medicina-general/revision-organos` | Crear revisión órganos |
| PUT | `/medicina-general/revision-organos/{id}` | Actualizar revisión órganos |
| DELETE | `/medicina-general/revision-organos/{id}` | Eliminar revisión órganos |
| GET | `/medicina-general/examen-fisico` | Listar exámenes físicos |
| GET | `/medicina-general/examen-fisico/{id}` | Ver examen físico |
| POST | `/medicina-general/examen-fisico` | Crear examen físico |
| PUT | `/medicina-general/examen-fisico/{id}` | Actualizar examen físico |
| DELETE | `/medicina-general/examen-fisico/{id}` | Eliminar examen físico |
| GET | `/medicina-general/diagnosticos` | Listar diagnósticos |
| GET | `/medicina-general/diagnosticos/{id}` | Ver diagnóstico |
| POST | `/medicina-general/diagnosticos` | Crear diagnóstico |
| PUT | `/medicina-general/diagnosticos/{id}` | Actualizar diagnóstico |
| DELETE | `/medicina-general/diagnosticos/{id}` | Eliminar diagnóstico |
| GET | `/medicina-general/planes-terapeuticos` | Listar planes terapéuticos |
| GET | `/medicina-general/planes-terapeuticos/{id}` | Ver plan terapéutico |
| POST | `/medicina-general/planes-terapeuticos` | Crear plan terapéutico |
| PUT | `/medicina-general/planes-terapeuticos/{id}` | Actualizar plan terapéutico |
| DELETE | `/medicina-general/planes-terapeuticos/{id}` | Eliminar plan terapéutico |
| GET | `/medicina-general/historial-evolucion` | Listar historial evolución |
| GET | `/medicina-general/historial-evolucion/{id}` | Ver historial evolución |
| POST | `/medicina-general/historial-evolucion` | Crear historial evolución |
| PUT | `/medicina-general/historial-evolucion/{id}` | Actualizar historial evolución |
| DELETE | `/medicina-general/historial-evolucion/{id}` | Eliminar historial evolución |

---

### Módulo: Medicina Ocupacional

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/medicina-ocupacional/cargos` | Listar cargos |
| GET | `/medicina-ocupacional/cargos/{id}` | Ver cargo |
| POST | `/medicina-ocupacional/cargos` | Crear cargo |
| PUT | `/medicina-ocupacional/cargos/{id}` | Actualizar cargo |
| DELETE | `/medicina-ocupacional/cargos/{id}` | Eliminar cargo |
| GET | `/medicina-ocupacional/usuario-tiene-cargo` | Listar asignaciones cargo |
| GET | `/medicina-ocupacional/usuario-tiene-cargo/{id}` | Ver asignación cargo |
| POST | `/medicina-ocupacional/usuario-tiene-cargo` | Crear asignación cargo |
| PUT | `/medicina-ocupacional/usuario-tiene-cargo/{id}` | Actualizar asignación cargo |
| DELETE | `/medicina-ocupacional/usuario-tiene-cargo/{id}` | Eliminar asignación cargo |
| GET | `/medicina-ocupacional/usuario-lugar-de-trabajo` | Listar lugares de trabajo |
| GET | `/medicina-ocupacional/usuario-lugar-de-trabajo/{id}` | Ver lugar trabajo |
| POST | `/medicina-ocupacional/usuario-lugar-de-trabajo` | Crear lugar trabajo |
| PUT | `/medicina-ocupacional/usuario-lugar-de-trabajo/{id}` | Actualizar lugar trabajo |
| DELETE | `/medicina-ocupacional/usuario-lugar-de-trabajo/{id}` | Eliminar lugar trabajo |
| GET | `/medicina-ocupacional/grupo-examen` | Listar grupos de examen |
| GET | `/medicina-ocupacional/grupo-examen/{id}` | Ver grupo examen |
| POST | `/medicina-ocupacional/grupo-examen` | Crear grupo examen |
| PUT | `/medicina-ocupacional/grupo-examen/{id}` | Actualizar grupo examen |
| DELETE | `/medicina-ocupacional/grupo-examen/{id}` | Eliminar grupo examen |
| GET | `/medicina-ocupacional/tipo-examen` | Listar tipos de examen |
| GET | `/medicina-ocupacional/tipo-examen/{id}` | Ver tipo examen |
| POST | `/medicina-ocupacional/tipo-examen` | Crear tipo examen |
| PUT | `/medicina-ocupacional/tipo-examen/{id}` | Actualizar tipo examen |
| DELETE | `/medicina-ocupacional/tipo-examen/{id}` | Eliminar tipo examen |
| GET | `/medicina-ocupacional/orden-examen` | Listar órdenes de examen |
| GET | `/medicina-ocupacional/orden-examen/{id}` | Ver orden examen |
| POST | `/medicina-ocupacional/orden-examen` | Crear orden examen |
| PUT | `/medicina-ocupacional/orden-examen/{id}` | Actualizar orden examen |
| DELETE | `/medicina-ocupacional/orden-examen/{id}` | Eliminar orden examen |
| GET | `/medicina-ocupacional/orden-examen-tipo` | Listar tipos de orden examen |
| POST | `/medicina-ocupacional/orden-examen-tipo` | Crear tipo orden examen |
| DELETE | `/medicina-ocupacional/orden-examen-tipo/{id}` | Eliminar tipo orden examen |
| GET | `/medicina-ocupacional/orden-examen-otros` | Listar otros exámenes |
| POST | `/medicina-ocupacional/orden-examen-otros` | Crear otro examen |
| PUT | `/medicina-ocupacional/orden-examen-otros/{id}` | Actualizar otro examen |
| DELETE | `/medicina-ocupacional/orden-examen-otros/{id}` | Eliminar otro examen |
| GET | `/medicina-ocupacional/receta` | Listar recetas |
| GET | `/medicina-ocupacional/receta/{id}` | Ver receta |
| POST | `/medicina-ocupacional/receta` | Crear receta |
| PUT | `/medicina-ocupacional/receta/{id}` | Actualizar receta |
| DELETE | `/medicina-ocupacional/receta/{id}` | Eliminar receta |
| GET | `/medicina-ocupacional/receta-cie` | Listar diagnósticos CIE-10 |
| POST | `/medicina-ocupacional/receta-cie` | Crear diagnóstico CIE-10 |
| PUT | `/medicina-ocupacional/receta-cie/{id}` | Actualizar diagnóstico CIE-10 |
| DELETE | `/medicina-ocupacional/receta-cie/{id}` | Eliminar diagnóstico CIE-10 |
| GET | `/medicina-ocupacional/linea-receta` | Listar líneas de receta |
| POST | `/medicina-ocupacional/linea-receta` | Crear línea receta |
| PUT | `/medicina-ocupacional/linea-receta/{id}` | Actualizar línea receta |
| DELETE | `/medicina-ocupacional/linea-receta/{id}` | Eliminar línea receta |
| GET | `/medicina-ocupacional/signo-alarma-receta` | Listar signos de alarma |
| POST | `/medicina-ocupacional/signo-alarma-receta` | Crear signo alarma |
| DELETE | `/medicina-ocupacional/signo-alarma-receta/{id}` | Eliminar signo alarma |
| GET | `/medicina-ocupacional/recomendacion-receta` | Listar recomendaciones |
| POST | `/medicina-ocupacional/recomendacion-receta` | Crear recomendación |
| PUT | `/medicina-ocupacional/recomendacion-receta/{id}` | Actualizar recomendación |
| DELETE | `/medicina-ocupacional/recomendacion-receta/{id}` | Eliminar recomendación |
| GET | `/medicina-ocupacional/listado-vacunas` | Listar vacunas |
| GET | `/medicina-ocupacional/listado-vacunas/{id}` | Ver vacuna |
| POST | `/medicina-ocupacional/listado-vacunas` | Crear vacuna |
| PUT | `/medicina-ocupacional/listado-vacunas/{id}` | Actualizar vacuna |
| DELETE | `/medicina-ocupacional/listado-vacunas/{id}` | Eliminar vacuna |
| GET | `/medicina-ocupacional/historial-vacunas` | Listar historial vacunas |
| GET | `/medicina-ocupacional/historial-vacunas/{id}` | Ver historial vacuna |
| POST | `/medicina-ocupacional/historial-vacunas` | Crear historial vacuna |
| PUT | `/medicina-ocupacional/historial-vacunas/{id}` | Actualizar historial vacuna |
| DELETE | `/medicina-ocupacional/historial-vacunas/{id}` | Eliminar historial vacuna |
| GET | `/medicina-ocupacional/reintegro-ueb` | Listar reintegros UEB |
| GET | `/medicina-ocupacional/reintegro-ueb/{id}` | Ver reintegro UEB |
| POST | `/medicina-ocupacional/reintegro-ueb` | Crear reintegro UEB |
| PUT | `/medicina-ocupacional/reintegro-ueb/{id}` | Actualizar reintegro UEB |
| DELETE | `/medicina-ocupacional/reintegro-ueb/{id}` | Eliminar reintegro UEB |
| GET | `/medicina-ocupacional/personal-nuevo` | Listar personal nuevo |
| GET | `/medicina-ocupacional/personal-nuevo/{id}` | Ver personal nuevo |
| POST | `/medicina-ocupacional/personal-nuevo` | Crear personal nuevo |
| PUT | `/medicina-ocupacional/personal-nuevo/{id}` | Actualizar personal nuevo |
| DELETE | `/medicina-ocupacional/personal-nuevo/{id}` | Eliminar personal nuevo |
| GET | `/medicina-ocupacional/cese-funciones` | Listar ceses de funciones |
| GET | `/medicina-ocupacional/cese-funciones/{id}` | Ver cese funciones |
| POST | `/medicina-ocupacional/cese-funciones` | Crear cese funciones |
| PUT | `/medicina-ocupacional/cese-funciones/{id}` | Actualizar cese funciones |
| DELETE | `/medicina-ocupacional/cese-funciones/{id}` | Eliminar cese funciones |
| GET | `/medicina-ocupacional/lista-vulnerabilidades` | Listar vulnerabilidades |
| GET | `/medicina-ocupacional/lista-vulnerabilidades/{id}` | Ver vulnerabilidad |
| POST | `/medicina-ocupacional/lista-vulnerabilidades` | Crear vulnerabilidad |
| PUT | `/medicina-ocupacional/lista-vulnerabilidades/{id}` | Actualizar vulnerabilidad |
| DELETE | `/medicina-ocupacional/lista-vulnerabilidades/{id}` | Eliminar vulnerabilidad |
| GET | `/medicina-ocupacional/grupo-vulnerable` | Listar grupos vulnerables |
| GET | `/medicina-ocupacional/grupo-vulnerable/{id}` | Ver grupo vulnerable |
| POST | `/medicina-ocupacional/grupo-vulnerable` | Crear grupo vulnerable |
| PUT | `/medicina-ocupacional/grupo-vulnerable/{id}` | Actualizar grupo vulnerable |
| DELETE | `/medicina-ocupacional/grupo-vulnerable/{id}` | Eliminar grupo vulnerable |
| GET | `/medicina-ocupacional/grupo-riesgo-psicosocial` | Listar riesgos psicosociales |
| GET | `/medicina-ocupacional/grupo-riesgo-psicosocial/{id}` | Ver riesgo psicosocial |
| POST | `/medicina-ocupacional/grupo-riesgo-psicosocial` | Crear riesgo psicosocial |
| PUT | `/medicina-ocupacional/grupo-riesgo-psicosocial/{id}` | Actualizar riesgo psicosocial |
| DELETE | `/medicina-ocupacional/grupo-riesgo-psicosocial/{id}` | Eliminar riesgo psicosocial |
| GET | `/medicina-ocupacional/listado-discapacidades` | Listar discapacidades |
| GET | `/medicina-ocupacional/listado-discapacidades/{id}` | Ver discapacidad |
| POST | `/medicina-ocupacional/listado-discapacidades` | Crear discapacidad |
| PUT | `/medicina-ocupacional/listado-discapacidades/{id}` | Actualizar discapacidad |
| DELETE | `/medicina-ocupacional/listado-discapacidades/{id}` | Eliminar discapacidad |
| GET | `/medicina-ocupacional/funcionarios-discapacidad` | Listar funcionarios con discapacidad |
| GET | `/medicina-ocupacional/funcionarios-discapacidad/{id}` | Ver funcionario discapacidad |
| POST | `/medicina-ocupacional/funcionarios-discapacidad` | Crear funcionario discapacidad |
| PUT | `/medicina-ocupacional/funcionarios-discapacidad/{id}` | Actualizar funcionario discapacidad |
| DELETE | `/medicina-ocupacional/funcionarios-discapacidad/{id}` | Eliminar funcionario discapacidad |
| GET | `/medicina-ocupacional/enfermedades-nuevas` | Listar enfermedades nuevas |
| GET | `/medicina-ocupacional/enfermedades-nuevas/{id}` | Ver enfermedad nueva |
| POST | `/medicina-ocupacional/enfermedades-nuevas` | Crear enfermedad nueva |
| PUT | `/medicina-ocupacional/enfermedades-nuevas/{id}` | Actualizar enfermedad nueva |
| DELETE | `/medicina-ocupacional/enfermedades-nuevas/{id}` | Eliminar enfermedad nueva |
| GET | `/medicina-ocupacional/enfermedades-catastroficas` | Listar enfermedades catastróficas |
| GET | `/medicina-ocupacional/enfermedades-catastroficas/{id}` | Ver enfermedad catastrófica |
| POST | `/medicina-ocupacional/enfermedades-catastroficas` | Crear enfermedad catastrófica |
| PUT | `/medicina-ocupacional/enfermedades-catastroficas/{id}` | Actualizar enfermedad catastrófica |
| DELETE | `/medicina-ocupacional/enfermedades-catastroficas/{id}` | Eliminar enfermedad catastrófica |
| GET | `/medicina-ocupacional/listado-embarazadas` | Listar embarazadas |
| GET | `/medicina-ocupacional/listado-embarazadas/{id}` | Ver embarazada |
| POST | `/medicina-ocupacional/listado-embarazadas` | Crear embarazada |
| PUT | `/medicina-ocupacional/listado-embarazadas/{id}` | Actualizar embarazada |
| DELETE | `/medicina-ocupacional/listado-embarazadas/{id}` | Eliminar embarazada |
| GET | `/medicina-ocupacional/influenza` | Listar influenza |
| GET | `/medicina-ocupacional/influenza/{id}` | Ver influenza |
| POST | `/medicina-ocupacional/influenza` | Crear influenza |
| PUT | `/medicina-ocupacional/influenza/{id}` | Actualizar influenza |
| DELETE | `/medicina-ocupacional/influenza/{id}` | Eliminar influenza |
| GET | `/medicina-ocupacional/ausentismo-laboral` | Listar ausentismo laboral |
| GET | `/medicina-ocupacional/ausentismo-laboral/{id}` | Ver ausentismo laboral |
| POST | `/medicina-ocupacional/ausentismo-laboral` | Crear ausentismo laboral |
| PUT | `/medicina-ocupacional/ausentismo-laboral/{id}` | Actualizar ausentismo laboral |
| DELETE | `/medicina-ocupacional/ausentismo-laboral/{id}` | Eliminar ausentismo laboral |
| GET | `/medicina-ocupacional/accidentes-laborales` | Listar accidentes laborales |
| GET | `/medicina-ocupacional/accidentes-laborales/{id}` | Ver accidente laboral |
| POST | `/medicina-ocupacional/accidentes-laborales` | Crear accidente laboral |
| PUT | `/medicina-ocupacional/accidentes-laborales/{id}` | Actualizar accidente laboral |
| DELETE | `/medicina-ocupacional/accidentes-laborales/{id}` | Eliminar accidente laboral |

---

### Módulo: Psicología

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/psicologia/motivo-consulta` | Listar motivos de consulta |
| GET | `/psicologia/motivo-consulta/{id}` | Ver motivo consulta |
| POST | `/psicologia/motivo-consulta` | Crear motivo consulta |
| PUT | `/psicologia/motivo-consulta/{id}` | Actualizar motivo consulta |
| DELETE | `/psicologia/motivo-consulta/{id}` | Eliminar motivo consulta |
| GET | `/psicologia/psicoanamnesis` | Listar psicoanamnesis |
| GET | `/psicologia/psicoanamnesis/{id}` | Ver psicoanamnesis |
| POST | `/psicologia/psicoanamnesis` | Crear psicoanamnesis |
| PUT | `/psicologia/psicoanamnesis/{id}` | Actualizar psicoanamnesis |
| DELETE | `/psicologia/psicoanamnesis/{id}` | Eliminar psicoanamnesis |
| GET | `/psicologia/historial-laboral` | Listar historial laboral |
| GET | `/psicologia/historial-laboral/{id}` | Ver historial laboral |
| POST | `/psicologia/historial-laboral` | Crear historial laboral |
| PUT | `/psicologia/historial-laboral/{id}` | Actualizar historial laboral |
| DELETE | `/psicologia/historial-laboral/{id}` | Eliminar historial laboral |
| GET | `/psicologia/historial-social` | Listar historial social |
| GET | `/psicologia/historial-social/{id}` | Ver historial social |
| POST | `/psicologia/historial-social` | Crear historial social |
| PUT | `/psicologia/historial-social/{id}` | Actualizar historial social |
| DELETE | `/psicologia/historial-social/{id}` | Eliminar historial social |
| GET | `/psicologia/historial-sexual` | Listar historial sexual |
| GET | `/psicologia/historial-sexual/{id}` | Ver historial sexual |
| POST | `/psicologia/historial-sexual` | Crear historial sexual |
| PUT | `/psicologia/historial-sexual/{id}` | Actualizar historial sexual |
| DELETE | `/psicologia/historial-sexual/{id}` | Eliminar historial sexual |
| GET | `/psicologia/patologias` | Listar patologías |
| GET | `/psicologia/patologias/{id}` | Ver patología |
| POST | `/psicologia/patologias` | Crear patología |
| PUT | `/psicologia/patologias/{id}` | Actualizar patología |
| DELETE | `/psicologia/patologias/{id}` | Eliminar patología |
| GET | `/psicologia/examen-estado-mental` | Listar exámenes estado mental |
| GET | `/psicologia/examen-estado-mental/{id}` | Ver examen estado mental |
| POST | `/psicologia/examen-estado-mental` | Crear examen estado mental |
| PUT | `/psicologia/examen-estado-mental/{id}` | Actualizar examen estado mental |
| DELETE | `/psicologia/examen-estado-mental/{id}` | Eliminar examen estado mental |
| GET | `/psicologia/pruebas-aplicadas` | Listar pruebas aplicadas |
| GET | `/psicologia/pruebas-aplicadas/{id}` | Ver prueba aplicada |
| POST | `/psicologia/pruebas-aplicadas` | Crear prueba aplicada |
| PUT | `/psicologia/pruebas-aplicadas/{id}` | Actualizar prueba aplicada |
| DELETE | `/psicologia/pruebas-aplicadas/{id}` | Eliminar prueba aplicada |
| GET | `/psicologia/analisis-resultados` | Listar análisis resultados |
| GET | `/psicologia/analisis-resultados/{id}` | Ver análisis resultados |
| POST | `/psicologia/analisis-resultados` | Crear análisis resultados |
| PUT | `/psicologia/analisis-resultados/{id}` | Actualizar análisis resultados |
| DELETE | `/psicologia/analisis-resultados/{id}` | Eliminar análisis resultados |
| GET | `/psicologia/conclusiones` | Listar conclusiones |
| GET | `/psicologia/conclusiones/{id}` | Ver conclusión |
| POST | `/psicologia/conclusiones` | Crear conclusión |
| PUT | `/psicologia/conclusiones/{id}` | Actualizar conclusión |
| DELETE | `/psicologia/conclusiones/{id}` | Eliminar conclusión |
| GET | `/psicologia/diagnostico` | Listar diagnósticos |
| GET | `/psicologia/diagnostico/{id}` | Ver diagnóstico |
| POST | `/psicologia/diagnostico` | Crear diagnóstico |
| PUT | `/psicologia/diagnostico/{id}` | Actualizar diagnóstico |
| DELETE | `/psicologia/diagnostico/{id}` | Eliminar diagnóstico |
| GET | `/psicologia/pronostico` | Listar prognósticos |
| GET | `/psicologia/pronostico/{id}` | Ver pronóstico |
| POST | `/psicologia/pronostico` | Crear pronóstico |
| PUT | `/psicologia/pronostico/{id}` | Actualizar pronóstico |
| DELETE | `/psicologia/pronostico/{id}` | Eliminar pronóstico |
| GET | `/psicologia/recomendacion` | Listar recomendaciones |
| GET | `/psicologia/recomendacion/{id}` | Ver recomendación |
| POST | `/psicologia/recomendacion` | Crear recomendación |
| PUT | `/psicologia/recomendacion/{id}` | Actualizar recomendación |
| DELETE | `/psicologia/recomendacion/{id}` | Eliminar recomendación |
| GET | `/psicologia/historial-evolucion` | Listar historial evolución |
| GET | `/psicologia/historial-evolucion/{id}` | Ver historial evolución |
| POST | `/psicologia/historial-evolucion` | Crear historial evolución |
| PUT | `/psicologia/historial-evolucion/{id}` | Actualizar historial evolución |
| DELETE | `/psicologia/historial-evolucion/{id}` | Eliminar historial evolución |
| GET | `/psicologia/parte-diario` | Listar parte diario |
| GET | `/psicologia/parte-diario/{id}` | Ver parte diario |
| POST | `/psicologia/parte-diario` | Crear parte diario |
| PUT | `/psicologia/parte-diario/{id}` | Actualizar parte diario |
| DELETE | `/psicologia/parte-diario/{id}` | Eliminar parte diario |

---

### Módulo: Odontología

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/odontologia/motivo-consulta` | Listar motivos de consulta |
| GET | `/odontologia/motivo-consulta/{id}` | Ver motivo consulta |
| POST | `/odontologia/motivo-consulta` | Crear motivo consulta |
| PUT | `/odontologia/motivo-consulta/{id}` | Actualizar motivo consulta |
| DELETE | `/odontologia/motivo-consulta/{id}` | Eliminar motivo consulta |
| GET | `/odontologia/examen` | Listar exámenes odontología |
| GET | `/odontologia/examen/{id}` | Ver examen odontología |
| POST | `/odontologia/examen` | Crear examen odontología |
| PUT | `/odontologia/examen/{id}` | Actualizar examen odontología |
| DELETE | `/odontologia/examen/{id}` | Eliminar examen odontología |
| GET | `/odontologia/enfermedad-periodontal` | Listar enfermedades periodontales |
| GET | `/odontologia/enfermedad-periodontal/{id}` | Ver enfermedad periodontal |
| POST | `/odontologia/enfermedad-periodontal` | Crear enfermedad periodontal |
| PUT | `/odontologia/enfermedad-periodontal/{id}` | Actualizar enfermedad periodontal |
| DELETE | `/odontologia/enfermedad-periodontal/{id}` | Eliminar enfermedad periodontal |
| GET | `/odontologia/historial-evolucion` | Listar historial evolución |
| GET | `/odontologia/historial-evolucion/{id}` | Ver historial evolución |
| POST | `/odontologia/historial-evolucion` | Crear historial evolución |
| PUT | `/odontologia/historial-evolucion/{id}` | Actualizar historial evolución |
| DELETE | `/odontologia/historial-evolucion/{id}` | Eliminar historial evolución |
| GET | `/odontologia/catalogo-insumos` | Listar catálogo de insumos |
| GET | `/odontologia/catalogo-insumos/{id}` | Ver insumo |
| POST | `/odontologia/catalogo-insumos` | Crear insumo |
| PUT | `/odontologia/catalogo-insumos/{id}` | Actualizar insumo |
| DELETE | `/odontologia/catalogo-insumos/{id}` | Eliminar insumo |
| GET | `/odontologia/parte-diario-odontologia` | Listar partes diarios |
| GET | `/odontologia/parte-diario-odontologia/{id}` | Ver parte diario |
| POST | `/odontologia/parte-diario-odontologia` | Crear parte diario |
| PUT | `/odontologia/parte-diario-odontologia/{id}` | Actualizar parte diario |
| DELETE | `/odontologia/parte-diario-odontologia/{id}` | Eliminar parte diario |
| GET | `/odontologia/insumos-paciente` | Listar insumos por paciente |
| GET | `/odontologia/insumos-paciente/{id}` | Ver insumo paciente |
| POST | `/odontologia/insumos-paciente` | Crear insumo paciente |
| PUT | `/odontologia/insumos-paciente/{id}` | Actualizar insumo paciente |
| DELETE | `/odontologia/insumos-paciente/{id}` | Eliminar insumo paciente |

**Filtros comunes:**
- `?id_usuario_paciente={id}` - Filtrar por paciente

---

### Módulo: Dashboard

| Método | Endpoint | Descripción | Rol requerido |
|--------|----------|-------------|---------------|
| GET | `/dashboard` | Dashboard general | Coordinador/Admin |
| GET | `/dashboard/doctores` | Estadísticas de doctores | Coordinador/Admin |
| GET | `/dashboard/estudiantes-frecuentes` | Estudiantes frecuentes | Coordinador/Admin |
| GET | `/dashboard/atenciones-tiempo` | Atenciones por tiempo | Coordinador/Admin |
| GET | `/dashboard/auditoria` | Auditoría del sistema | Administrador |

---

### Módulo: Auditoría

| Método | Endpoint | Descripción | Rol requerido |
|--------|----------|-------------|---------------|
| GET | `/audit-logs` | Listar logs de auditoría | Coordinador/Admin |
| GET | `/security-logs` | Listar logs de seguridad | Administrador |
| GET | `/security-logs/blocked-ips` | Listar IPs bloqueadas | Administrador |
| GET | `/login-attempts` | Listar intentos de login | Administrador |

---

### Módulo: Reportes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/reportes` | Generar reporte |
| GET | `/reportes/pdf` | Generar reporte PDF |
| GET | `/reportes/catalogos` | Listar catálogos |

---

## Filtros Comunes

Algunos endpoints soportan filtros por paciente:

```
GET /api/v1/odontologia/motivo-consulta?id_usuario_paciente=5
GET /api/v1/medicina-general/signos-vitales?id_usuario_paciente=5
```

---

## Archivos de Prueba

- `public/test-odontologia.html` - Interfaz de prueba para odontología
- `public/test-medicina-general.html` - Interfaz de prueba para medicina general

---

## Middlewares

- `auth:sanctum` - Requiere autenticación
- `force_password_change` - Fuerza cambio de contraseña
- `role:{nombre_rol}` - Requiere rol específico

---

## Respuestas Comunes

### Éxito (200/201)
```json
{
  "data": { ... },
  "message": "Operation successful"
}
```

### Error (404)
```json
{
  "message": "Resource not found"
}
```

### Error de Validación (422)
```json
{
  "message": "The given data was invalid",
  "errors": {
    "field": ["Error message"]
  }
}
```

---

## Licencia

MIT
