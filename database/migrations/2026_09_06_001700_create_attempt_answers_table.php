<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('section_position')->nullable();
            $table->unsignedSmallInteger('question_position');
            $table->json('question_snapshot');
            $table->json('context_snapshot')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 6, 2)->nullable();
            $table->decimal('max_points', 6, 2)->default(1.00);
            $table->unsignedInteger('save_version')->default(0);
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
            $table->index(['attempt_id', 'is_correct']);
            $table->index(['attempt_id', 'section_position', 'question_position'], 'attempt_answers_attempt_positions_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
    }
};
