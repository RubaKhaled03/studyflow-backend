<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reflections', function (Blueprint $table) {
            $table->text('achieved')->nullable();
            $table->text('difficult')->nullable();
            $table->text('learned')->nullable();
            $table->text('improve_next')->nullable();
            $table->text('gratitude')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reflections', function (Blueprint $table) {
            $table->dropColumn(['achieved', 'difficult', 'learned', 'improve_next', 'gratitude']);
        });
    }
};
