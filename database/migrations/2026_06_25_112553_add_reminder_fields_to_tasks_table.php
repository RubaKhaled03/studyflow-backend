<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('reminder_enabled')->default(false);
            $table->integer('reminder_timing_value')->nullable();
            $table->enum('reminder_timing_unit', ['minutes', 'hours', 'days'])->nullable();
            $table->string('reminder_channel')->nullable();
            // عشان ما نولّد notification لنفس الـ task أكتر من مرة
            $table->timestamp('reminder_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_enabled',
                'reminder_timing_value',
                'reminder_timing_unit',
                'reminder_channel',
                'reminder_sent_at',
            ]);
        });
    }
};
