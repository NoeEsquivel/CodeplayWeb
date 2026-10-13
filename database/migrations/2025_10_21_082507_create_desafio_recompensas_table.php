<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('desafio_recompensas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->integer('nivel_desafio'); // Número del nivel (1-20)
            $table->boolean('completado_primera_vez')->default(false);
            $table->integer('puntos_obtenidos')->default(0);
            $table->timestamp('ultima_recompensa')->nullable();
            $table->timestamps();

            $table->unique(['usuario_id', 'nivel_desafio']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('desafio_recompensas');
    }
};
