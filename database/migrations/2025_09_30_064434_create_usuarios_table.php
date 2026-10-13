<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('clave'); // Para hash de contraseña
            $table->enum('rol', ['Alumno', 'Administrador', 'Master'])->nullable();
            $table->integer('puntos_totales')->nullable()->default(0);
            $table->integer('puntos_usados')->nullable()->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
};
