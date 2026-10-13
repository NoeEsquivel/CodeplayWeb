<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tutoriales', function (Blueprint $table) {
            $table->string('slug', 200)->after('titulo');
        });
    }

    public function down()
    {
        Schema::table('tutoriales', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
