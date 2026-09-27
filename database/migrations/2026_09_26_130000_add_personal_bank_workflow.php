<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_bancos', function (Blueprint $table) {
            $table->string('materia', 100)->nullable()->after('categoria');
            $table->string('estado', 20)->default('borrador')->after('materia');
            $table->index(['creador_usuario_id', 'estado', 'activo'], 'tb_creador_estado_activo_idx');
        });

        DB::table('test_bancos')->where('origen', 'sistema')->update([
            'estado' => 'publicado',
            'materia' => DB::raw('categoria'),
        ]);
    }

    public function down(): void
    {
        Schema::table('test_bancos', function (Blueprint $table) {
            $table->dropIndex('tb_creador_estado_activo_idx');
            $table->dropColumn(['materia', 'estado']);
        });
    }
};

