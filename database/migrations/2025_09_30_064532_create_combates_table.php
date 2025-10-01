<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('combates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('enemigo_id')->constrained('enemigos')->onDelete('cascade');
            $table->enum('resultado', ['victoria', 'derrota', 'empate']);
            $table->integer('vida_restante_usuario');
            $table->integer('vida_restante_enemigo');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('combates');
    }
};