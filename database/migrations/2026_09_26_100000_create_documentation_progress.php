<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progreso_documentacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('documento', 40);
            $table->timestamp('visto_at')->useCurrent();
            $table->timestamps();
            $table->unique(['usuario_id', 'documento']);
            $table->index(['usuario_id', 'visto_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progreso_documentacion');
    }
};
