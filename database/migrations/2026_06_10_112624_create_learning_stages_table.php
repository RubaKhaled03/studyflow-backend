<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_plan_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('target_duration')->nullable();
            $table->enum('status', ['not-started', 'active', 'completed'])->default('not-started');
            $table->text('goals')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_stages');
    }
};
