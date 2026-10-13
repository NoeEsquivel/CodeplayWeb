<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('preguntas_test', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->text('pregunta');
            $table->string('opcion_a', 255);
            $table->string('opcion_b', 255);
            $table->string('opcion_c', 255);
            $table->string('opcion_d', 255);
            $table->enum('respuesta_correcta', ['a', 'b', 'c', 'd']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('preguntas_test');
    }
};
