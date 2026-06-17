<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('week_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->integer('week_number');
            $table->string('week_title')->nullable();
            $table->string('title');
            $table->enum('type', ['study_task', 'midterm', 'final', 'quiz', 'assignment', 'project', 'presentation', 'lab', 'submission', 'reading_session']);
            $table->date('date');
            $table->time('time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['upcoming', 'completed', 'submitted', 'missed', 'graded'])->default('upcoming');
            $table->enum('priority', ['low', 'normal', 'important', 'urgent'])->default('normal');
            $table->string('location')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->boolean('completed')->default(false);
            $table->boolean('submitted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('week_items');
    }
};
