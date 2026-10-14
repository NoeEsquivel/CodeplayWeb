<?php

namespace Tests\Feature;

use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OperationalCommandsTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_inactive_user_report_lists_only_students_older_than_six_months(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00'));
        $suffix = str_replace('.', '', uniqid('', true));
        $college = Colegio::create([
            'nombre' => 'Colegio comandos '.$suffix,
            'codigo' => 'CMD-'.$suffix,
            'activo' => true,
        ]);

        $inactive = $this->user($college->id, 'Alumno inactivo '.$suffix, Usuario::ROL_ALUMNO);
        $inactive->forceFill(['created_at' => now()->subYear(), 'ultimo_acceso' => null])->saveQuietly();

        $active = $this->user($college->id, 'Alumno activo '.$suffix, Usuario::ROL_ALUMNO);
        $active->forceFill(['created_at' => now()->subYear(), 'ultimo_acceso' => now()->subMonth()])->saveQuietly();

        $teacher = $this->user($college->id, 'Profesor antiguo '.$suffix, Usuario::ROL_PROFESOR);
        $teacher->forceFill(['created_at' => now()->subYear(), 'ultimo_acceso' => null])->saveQuietly();

        $this->artisan('usuarios:verificar-inactivos')
            ->expectsOutputToContain('USUARIOS QUE SERÁN ELIMINADOS')
            ->expectsOutputToContain($inactive->nombre)
            ->doesntExpectOutputToContain($active->nombre)
            ->doesntExpectOutputToContain($teacher->nombre)
            ->assertSuccessful();
    }

    public function test_backup_command_rejects_memory_database_and_creates_verified_sqlite_copy(): void
    {
        $originalDefault = config('database.default');
        $originalSqliteDatabase = config('database.connections.sqlite.database');
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        $this->artisan('codeplay:backup')->expectsOutputToContain('no se puede respaldar')->assertFailed();

        $sourceDirectory = storage_path('framework/testing');
        File::ensureDirectoryExists($sourceDirectory);
        $source = $sourceDirectory.DIRECTORY_SEPARATOR.'coverage-backup-'.uniqid().'.sqlite';
        File::put($source, 'base de datos de prueba');
        config()->set('database.connections.sqlite.database', $source);

        try {
            $this->artisan('codeplay:backup', ['--keep' => 1000])
                ->expectsOutputToContain('Respaldo creado y verificado.')
                ->assertSuccessful();

            $pattern = storage_path('app/backups/'.pathinfo($source, PATHINFO_FILENAME).'_*.sqlite');
            $backups = glob($pattern) ?: [];
            $this->assertCount(1, $backups);
            $this->assertSame(hash_file('sha256', $backups[0]), trim(strtok(File::get($backups[0].'.sha256'), ' ')));
        } finally {
            config()->set('database.default', $originalDefault);
            config()->set('database.connections.sqlite.database', $originalSqliteDatabase);
            File::delete($source);
            foreach (glob(storage_path('app/backups/'.pathinfo($source, PATHINFO_FILENAME).'_*')) ?: [] as $generated) {
                File::delete($generated);
            }
        }
    }

    private function user(int $collegeId, string $name, string $role): Usuario
    {
        return Usuario::create([
            'colegio_id' => $collegeId,
            'nombre' => $name,
            'clave' => Hash::make('Clave1234'),
            'rol' => $role,
            'activo' => true,
            'cambio_clave_requerido' => false,
        ]);
    }
}