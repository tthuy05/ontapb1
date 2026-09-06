<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercise_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('in_progress');
            $table->string('completion_reason', 20)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score_awarded', 8, 2)->default(0.00);
            $table->decimal('max_score', 8, 2)->default(0.00);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('incorrect_count')->default(0);
            $table->unsignedInteger('unanswered_count')->default(0);
            $table->unsignedInteger('ungraded_count')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('configuration_snapshot');
            $table->timestamps();

            $table->index(['status', 'started_at']);
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
