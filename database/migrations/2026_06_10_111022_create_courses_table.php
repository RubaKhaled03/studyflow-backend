<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('code')->nullable();
            $table->string('instructor')->nullable();
            $table->integer('credits')->default(3);
            $table->enum('status', ['current', 'completed', 'planned'])->default('current');
            $table->string('image_url')->nullable();
            $table->integer('duration_weeks')->default(16);
            $table->integer('current_week')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('progress')->default(0);
            $table->string('final_grade')->nullable();
            $table->integer('numeric_grade')->nullable();
            $table->string('academic_period')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
