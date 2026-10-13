<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['consumible', 'equipamiento', 'material', 'especial']);
            $table->text('descripcion');
            $table->integer('precio');
            $table->string('efecto', 255)->nullable(); // Ej: "curar 50 vida", "aumentar daño 10%"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
};
