<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('rol')->index();
        });

        Schema::create('registros_administrativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('objetivo_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('colegio_id')->nullable()->constrained('colegios')->nullOnDelete();
            $table->string('accion', 80)->index();
            $table->string('entidad_tipo', 80)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('detalles')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['colegio_id', 'created_at']);
            $table->index(['actor_usuario_id', 'created_at']);
            $table->index(['entidad_tipo', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_administrativos');

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
