<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_mode_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('exam_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_mode_states');
    }
};
