<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            $table->decimal('points', 6, 2)->default(1.00);
            $table->timestamps();

            $table->unique(['exercise_id', 'question_id']);
            $table->unique(['exercise_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_questions');
    }
};
