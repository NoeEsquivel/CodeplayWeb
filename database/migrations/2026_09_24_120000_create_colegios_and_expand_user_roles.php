<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colegios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->string('codigo', 30)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `usuarios` MODIFY `rol` VARCHAR(40) NULL');
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite representa enum mediante CHECK y no permite alterar esa restricción sin reconstruir
            // la tabla. Mantenerla desactivada evita romper sus claves foráneas durante las pruebas.
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        DB::table('usuarios')
            ->where('rol', 'Administrador')
            ->update(['rol' => 'Profesor']);

        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('colegio_id')
                ->nullable()
                ->after('id')
                ->constrained('colegios')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('colegio_id');
        });

        DB::table('usuarios')
            ->whereIn('rol', ['Profesor', 'AdministradorColegio'])
            ->update(['rol' => 'Administrador']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `usuarios` MODIFY `rol` ENUM('Alumno', 'Administrador', 'Master') NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = OFF');
        }

        Schema::dropIfExists('colegios');
    }
};