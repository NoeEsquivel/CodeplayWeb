<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_banco_clase', function (Blueprint $table) {
            $table->foreignId('banco_id')->constrained('test_bancos')->cascadeOnDelete();
            $table->foreignId('clase_id')->constrained('clases')->cascadeOnDelete();
            $table->foreignId('asignado_por_usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamp('disponible_desde')->nullable();
            $table->timestamp('disponible_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->primary(['banco_id', 'clase_id']);
            $table->index(['clase_id', 'activo', 'disponible_desde', 'disponible_hasta'], 'tbc_clase_disponibilidad_idx');
            $table->index(['asignado_por_usuario_id', 'activo'], 'tbc_profesor_activo_idx');
        });

        Schema::table('test_sesiones', function (Blueprint $table) {
            $table->timestamp('disponible_desde')->nullable()->after('configuracion');
            $table->timestamp('disponible_hasta')->nullable()->after('disponible_desde');
            $table->index(['clase_id', 'estado', 'disponible_desde', 'disponible_hasta'], 'ts_clase_estado_disponibilidad_idx');
        });
    }

    public function down(): void
    {
        Schema::table('test_sesiones', function (Blueprint $table) {
            $table->dropIndex('ts_clase_estado_disponibilidad_idx');
            $table->dropColumn(['disponible_desde', 'disponible_hasta']);
        });
        Schema::dropIfExists('test_banco_clase');
    }
};
