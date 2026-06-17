<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('focus_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('duration_minutes');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->enum('mode', ['pomodoro', 'stopwatch'])->default('pomodoro');
            $table->boolean('completed')->default(true);
            $table->string('linked_task_id')->nullable();
            $table->string('linked_course_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('focus_sessions');
    }
};
