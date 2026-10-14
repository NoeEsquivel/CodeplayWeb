<?php

namespace Tests\Unit;

use App\Models\Clase;
use App\Models\TestBanco;
use App\Models\TestBancoPregunta;
use App\Models\TestHistorialRespuesta;
use App\Models\TestResultadoSesion;
use App\Models\TestSesionParticipante;
use App\Models\TestSesionPregunta;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class DomainModelBehaviorTest extends TestCase
{
    public function test_usuario_reports_full_name_and_identifies_every_role(): void
    {
        $usuario = new Usuario(['nombre' => 'usuario.corto', 'nombre_completo' => '  Ana Maria Perez  ']);

        $this->assertSame('Ana Maria Perez', $usuario->nombreParaReportes());
        $usuario->nombre_completo = '   ';
        $this->assertSame('usuario.corto', $usuario->nombreParaReportes());

        $usuario->rol = Usuario::ROL_PROFESOR;
        $this->assertTrue($usuario->esProfesor());
        $this->assertTrue($usuario->esAdministrador());
        $this->assertTrue($usuario->requiereCorreoVerificado());

        $usuario->rol = Usuario::ROL_ADMINISTRADOR_COLEGIO;
        $this->assertTrue($usuario->esAdministradorColegio());
        $this->assertTrue($usuario->requiereCorreoVerificado());

        $usuario->rol = Usuario::ROL_MASTER;
        $this->assertTrue($usuario->esMaster());
        $this->assertTrue($usuario->requiereCorreoVerificado());

        $usuario->rol = Usuario::ROL_ALUMNO;
        $this->assertTrue($usuario->esAlumno());
        $this->assertFalse($usuario->requiereCorreoVerificado());
    }

    public function test_usuario_exposes_all_domain_relationships(): void
    {
        $usuario = new Usuario();

        $this->assertInstanceOf(BelongsTo::class, $usuario->colegio());
        $this->assertInstanceOf(BelongsToMany::class, $usuario->clasesComoProfesor());
        $this->assertInstanceOf(BelongsToMany::class, $usuario->clasesComoAlumno());
        $this->assertInstanceOf(HasOne::class, $usuario->twoFactorAuthentication());
        $this->assertInstanceOf(HasMany::class, $usuario->bancosTestCreados());
        $this->assertInstanceOf(BelongsToMany::class, $usuario->bancosTestBiblioteca());
        $this->assertInstanceOf(HasMany::class, $usuario->progresoNiveles());
        $this->assertInstanceOf(HasMany::class, $usuario->resultadosTest());
        $this->assertInstanceOf(HasMany::class, $usuario->combates());
        $this->assertInstanceOf(HasMany::class, $usuario->inventario());
        $this->assertInstanceOf(HasMany::class, $usuario->progresoTutoriales());
    }

    public function test_test_reporting_models_expose_relations_and_prefer_current_full_name(): void
    {
        $history = new TestHistorialRespuesta();
        $this->assertInstanceOf(BelongsTo::class, $history->sesion());
        $this->assertInstanceOf(BelongsTo::class, $history->alumno());
        $this->assertInstanceOf(BelongsTo::class, $history->banco());

        $result = new TestResultadoSesion(['nombre_alumno' => 'Nombre historico']);
        $result->setRelation('usuario', new Usuario([
            'nombre' => 'usuario.actual',
            'nombre_completo' => 'Nombre Completo Actual',
        ]));
        $this->assertSame('Nombre Completo Actual', $result->nombreAlumnoReporte());
        $result->setRelation('usuario', null);
        $this->assertSame('Nombre historico', $result->nombreAlumnoReporte());
        $this->assertInstanceOf(BelongsTo::class, $result->sesion());
        $this->assertInstanceOf(BelongsTo::class, $result->participante());
        $this->assertInstanceOf(BelongsTo::class, $result->usuario());
        $this->assertInstanceOf(BelongsTo::class, $result->colegio());
        $this->assertInstanceOf(BelongsTo::class, $result->clase());
    }

    public function test_interactive_test_models_expose_navigation_relationships(): void
    {
        $participant = new TestSesionParticipante();
        $this->assertInstanceOf(BelongsTo::class, $participant->sesion());
        $this->assertInstanceOf(BelongsTo::class, $participant->usuario());
        $this->assertInstanceOf(HasMany::class, $participant->respuestas());

        $sessionQuestion = new TestSesionPregunta();
        $this->assertInstanceOf(BelongsTo::class, $sessionQuestion->sesion());
        $this->assertInstanceOf(BelongsTo::class, $sessionQuestion->pregunta());
        $this->assertInstanceOf(HasMany::class, $sessionQuestion->respuestas());

        $bankQuestion = new TestBancoPregunta();
        $this->assertInstanceOf(BelongsTo::class, $bankQuestion->banco());
        $this->assertInstanceOf(HasMany::class, $bankQuestion->emisiones());
    }

    public function test_bank_and_class_relationship_configuration_matches_the_domain(): void
    {
        $bank = new TestBanco(['origen' => 'sistema']);
        $this->assertTrue($bank->esSistema());
        $bank->origen = 'profesor';
        $this->assertFalse($bank->esSistema());
        $this->assertInstanceOf(BelongsTo::class, $bank->colegio());
        $this->assertInstanceOf(BelongsTo::class, $bank->creador());
        $this->assertInstanceOf(HasMany::class, $bank->preguntas());
        $this->assertInstanceOf(BelongsToMany::class, $bank->usuariosBiblioteca());
        $this->assertInstanceOf(BelongsToMany::class, $bank->sesiones());
        $this->assertInstanceOf(BelongsToMany::class, $bank->clasesAsignadas());

        $class = new Clase();
        $this->assertInstanceOf(BelongsTo::class, $class->colegio());
        $this->assertInstanceOf(BelongsToMany::class, $class->profesores());
        $this->assertInstanceOf(BelongsToMany::class, $class->alumnos());
        $this->assertInstanceOf(BelongsToMany::class, $class->bancosTestAsignados());
    }

    public function test_class_rejects_members_with_wrong_role_or_college(): void
    {
        $class = new Clase(['colegio_id' => 10]);

        try {
            $class->agregarProfesor(new Usuario(['rol' => Usuario::ROL_ALUMNO, 'colegio_id' => 10]));
            $this->fail('Una clase no debe aceptar a un alumno como profesor.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('Solo un usuario Profesor puede asignarse como profesor de una clase.', $exception->getMessage());
        }

        try {
            $class->agregarAlumno(new Usuario(['rol' => Usuario::ROL_PROFESOR, 'colegio_id' => 10]));
            $this->fail('Una clase no debe aceptar a un profesor como alumno.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('Solo un usuario Alumno puede asignarse como alumno de una clase.', $exception->getMessage());
        }

        try {
            $class->agregarProfesor(new Usuario(['rol' => Usuario::ROL_PROFESOR, 'colegio_id' => 99]));
            $this->fail('No se deben mezclar usuarios y clases de colegios distintos.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('El usuario y la clase deben pertenecer al mismo colegio.', $exception->getMessage());
        }
    }
}
