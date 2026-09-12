<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speaking_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('speaking_prompt_id')->constrained()->restrictOnDelete();
            $table->foreignId('attempt_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('exam_section_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 16)->default('draft');
            $table->json('prompt_snapshot');
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->json('self_assessment')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('save_version')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['speaking_prompt_id', 'status']);
            $table->index(['attempt_id', 'status']);
            $table->index(['exam_section_item_id', 'status']);
            $table->unique(['attempt_id', 'exam_section_item_id'], 'speaking_submissions_attempt_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('speaking_submissions');
    }
};
