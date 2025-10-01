<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('niveles_desafio', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->unique();
            $table->text('descripcion');
            $table->enum('dificultad', ['facil', 'medio', 'dificil', 'experto']);
            $table->integer('puntos_otorgados')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('niveles_desafio');
    }
};