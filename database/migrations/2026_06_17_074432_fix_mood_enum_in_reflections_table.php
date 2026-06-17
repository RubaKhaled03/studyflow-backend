<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reflections', function (Blueprint $table) {
            $table->string('mood_new')->nullable();
        });

        DB::statement('UPDATE reflections SET mood_new = mood');

        Schema::table('reflections', function (Blueprint $table) {
            $table->dropColumn('mood');
        });

        Schema::table('reflections', function (Blueprint $table) {
            $table->renameColumn('mood_new', 'mood');
        });
    }

    public function down(): void
    {
        //
    }
};
