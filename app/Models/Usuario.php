<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Model
{
    use HasFactory;

    public const ROL_ALUMNO = 'Alumno';
    public const ROL_PROFESOR = 'Profesor';
    public const ROL_ADMINISTRADOR_COLEGIO = 'AdministradorColegio';
    public const ROL_MASTER = 'Master';

    public const ROLES = [
        self::ROL_ALUMNO,
        self::ROL_PROFESOR,
        self::ROL_ADMINISTRADOR_COLEGIO,
        self::ROL_MASTER,
    ];

    protected $table = 'usuarios';

    protected $fillable = [
        'colegio_id', 'nombre', 'nombre_completo', 'email', 'email_verified_at', 'clave', 'rol', 'activo',
        'cambio_clave_requerido', 'session_version', 'puntos_totales', 'puntos_usados',
    ];

    protected $hidden = ['clave', 'session_version'];

    protected $casts = [
        'colegio_id' => 'integer',
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
        'cambio_clave_requerido' => 'boolean',
        'session_version' => 'integer',
        'ultimo_acceso' => 'datetime',
        'puntos_totales' => 'integer',
        'puntos_usados' => 'integer',
        'lux_bienvenida_vista_at' => 'datetime',
    ];

    protected $attributes = [
        'puntos_totales' => 0,
        'puntos_usados' => 0,
    ];

    public function nombreParaReportes(): string { return trim((string) $this->nombre_completo) ?: (string) $this->nombre; }

    public function esProfesor(): bool { return $this->rol === self::ROL_PROFESOR; }

    public function esAdministrador(): bool { return $this->esProfesor(); }

    public function esAdministradorColegio(): bool { return $this->rol === self::ROL_ADMINISTRADOR_COLEGIO; }

    public function esMaster(): bool { return $this->rol === self::ROL_MASTER; }

    public function esAlumno(): bool { return $this->rol === self::ROL_ALUMNO; }

    public function requiereCorreoVerificado(): bool
    {
        return in_array($this->rol, [self::ROL_PROFESOR, self::ROL_ADMINISTRADOR_COLEGIO, self::ROL_MASTER], true);
    }

    public function colegio(): BelongsTo { return $this->belongsTo(Colegio::class); }

    public function clasesComoProfesor(): BelongsToMany
    {
        return $this->belongsToMany(Clase::class, 'clase_profesor')->withTimestamps();
    }

    public function clasesComoAlumno(): BelongsToMany
    {
        return $this->belongsToMany(Clase::class, 'clase_alumno')->withTimestamps();
    }

    public function twoFactorAuthentication(): HasOne
    {
        return $this->hasOne(TwoFactorAuthentication::class);
    }

    public function bancosTestCreados() { return $this->hasMany(TestBanco::class, 'creador_usuario_id'); }

    public function bancosTestBiblioteca(): BelongsToMany
    {
        return $this->belongsToMany(TestBanco::class, 'test_biblioteca_usuario', 'usuario_id', 'banco_id')
            ->withPivot('agregado_por_codigo')
            ->withTimestamps();
    }

    public function progresoNiveles() { return $this->hasMany(ProgresoNivel::class); }
    public function resultadosTest() { return $this->hasMany(ResultadoTest::class); }
    public function combates() { return $this->hasMany(Combate::class); }
    public function inventario() { return $this->hasMany(Inventario::class); }
    public function progresoTutoriales() { return $this->hasMany(ProgresoTutorial::class); }
}
