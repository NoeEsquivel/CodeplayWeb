<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tutoriales', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->enum('dificultad', ['facil', 'medio', 'dificil']);
            $table->integer('puntos_otorgados')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tutoriales');
    }
};