<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The name of the guard for this model.
     *
     * @var string
     */
    protected $guard_name = 'sanctum';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'clave_temporal',
        'must_change_password',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'clave_temporal',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function datosIdentificacion(): HasOne
    {
        return $this->hasOne(DatosIdentificacion::class, 'id_usuario');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(UsuarioTieneHijo::class, 'id_usuario');
    }

    public function alergias(): HasMany
    {
        return $this->hasMany(UsuarioTieneAlergia::class, 'id_usuario');
    }

    public function discapacidades(): HasMany
    {
        return $this->hasMany(UsuarioTieneDiscapacidad::class, 'id_usuario');
    }

    public function estudioCarrera(): HasOne
    {
        return $this->hasOne(UsuarioEstudiaCarrera::class, 'id_usuario');
    }

    public function autopercepcion(): HasOne
    {
        return $this->hasOne(DatosAutopercepcionCiudadana::class, 'id_usuario');
    }

    /**
     * Get the gender (genero) through autopercepcion relationship.
     */
    public function genero(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            IdentificacionGenero::class,
            DatosAutopercepcionCiudadana::class,
            'id_usuario',
            'id',
            'id',
            'id_genero'
        );
    }

    /**
     * Get the cargo (job position) for medical staff.
     */
    public function cargo(): HasOne
    {
        return $this->hasOne(ListadoCargoPersonalMedicoocupacional::class, 'id_usuario');
    }

    public function direcciones(): HasMany
    {
        return $this->hasMany(DireccionUsuario::class, 'id_usuario');
    }

    public function contactosEmergencia(): HasMany
    {
        return $this->hasMany(ContactoEmergencia::class, 'id_usuario');
    }

    public function foto(): HasOne
    {
        return $this->hasOne(FotoUsuario::class, 'id_usuario');
    }

    public function fichaSocioeconomica(): HasOne
    {
        return $this->hasOne(FichaSocioeconomica::class, 'id_usuario');
    }

    public function cargoMedico(): HasOne
    {
        return $this->hasOne(ListadoCargoPersonalMedicoocupacional::class, 'id_usuario');
    }

    public function lugaresDeTrabajo(): BelongsToMany
    {
        return $this->belongsToMany(LugarTrabajo::class, 'usuario_lugar_de_trabajo', 'id_usuario', 'id_lugar_trabajo');
    }

    public function pacientesACargo(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_tiene_cargo', 'id_usuario_doctor', 'id_usuario_paciente');
    }

    public function doctores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_tiene_cargo', 'id_usuario_paciente', 'id_usuario_doctor');
    }

    public function tipoSangreActual(): HasOne
    {
        return $this->hasOne(UsuarioTipoSangre::class, 'id_usuario')->latestOfMany();
    }
}
