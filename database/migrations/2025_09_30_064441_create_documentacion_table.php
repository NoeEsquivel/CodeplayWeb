<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documentacion', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('contenido');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentacion');
    }
};