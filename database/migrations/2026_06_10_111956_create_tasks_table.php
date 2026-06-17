<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['general', 'study-task', 'assignment', 'quiz', 'exam', 'self-learning-milestone'])->default('general');
            $table->enum('source_module', ['general', 'course', 'self-learning'])->default('general');
            $table->string('linked_course_title')->nullable();
            $table->string('linked_week_id')->nullable();
            $table->string('linked_week_label')->nullable();
            $table->string('linked_learning_plan_id')->nullable();
            $table->string('linked_learning_plan_title')->nullable();
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['todo', 'in-progress', 'done'])->default('todo');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
