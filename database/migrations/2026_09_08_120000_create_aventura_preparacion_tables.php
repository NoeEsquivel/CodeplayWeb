<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aventura_personajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $table->unsignedInteger('nivel')->default(1);
            $table->unsignedInteger('experiencia')->default(0);
            foreach (['dano', 'magia', 'armadura', 'inteligencia', 'vitalidad'] as $stat) {
                $table->unsignedInteger($stat)->default(1);
            }
            $table->json('ultimo_aumento')->nullable();
            $table->timestamps();
        });
        Schema::create('aventura_expediciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('estado', 20)->default('preparacion');
            $table->json('equipo');
            $table->unsignedInteger('inversion')->default(0);
            $table->unsignedInteger('version')->default(0);
            $table->timestamp('finalizada_at')->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'estado']);
        });
        Schema::create('aventura_experiencia_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('evento', 120);
            $table->unsignedInteger('experiencia');
            $table->timestamps();
            $table->unique(['usuario_id', 'evento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aventura_experiencia_eventos');
        Schema::dropIfExists('aventura_expediciones');
        Schema::dropIfExists('aventura_personajes');
    }
};
