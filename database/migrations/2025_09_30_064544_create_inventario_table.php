<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('cantidad')->default(1);
            $table->timestamps();

            // Un usuario no puede tener el mismo item duplicado
            $table->unique(['usuario_id', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventario');
    }
};
