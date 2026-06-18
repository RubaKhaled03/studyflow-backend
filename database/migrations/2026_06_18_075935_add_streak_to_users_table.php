<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('streak_current')->default(0);
            $table->integer('streak_longest')->default(0);
            $table->string('streak_last_active_date')->nullable();
            $table->json('streak_active_days')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['streak_current', 'streak_longest', 'streak_last_active_date', 'streak_active_days']);
        });
    }
};
