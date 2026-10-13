<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enemigos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('vida');
            $table->integer('magia')->default(0);
            $table->integer('daño');
            $table->integer('defensa');
            $table->integer('velocidad');
            $table->integer('recompensa_puntos');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('enemigos');
    }
};
