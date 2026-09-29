<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('nombre_completo', 160)->nullable()->after('nombre');
        });

        DB::table('usuarios')->select('id', 'nombre')->orderBy('id')->eachById(function ($usuario) {
            DB::table('usuarios')->where('id', $usuario->id)->update(['nombre_completo' => $usuario->nombre]);
        });

        Schema::table('aventura_personajes', function (Blueprint $table) {
            $table->string('nombre_aventurero', 60)->nullable()->after('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::table('aventura_personajes', fn (Blueprint $table) => $table->dropColumn('nombre_aventurero'));
        Schema::table('usuarios', fn (Blueprint $table) => $table->dropColumn('nombre_completo'));
    }
};
