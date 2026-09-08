<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('writing_prompt_id')->constrained()->restrictOnDelete();
            $table->foreignId('attempt_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('exam_section_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 16)->default('draft');
            $table->longText('response_text');
            $table->unsignedSmallInteger('word_count')->default(0);
            $table->json('self_check')->nullable();
            $table->json('prompt_snapshot');
            $table->unsignedInteger('save_version')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['writing_prompt_id', 'status']);
            $table->index(['attempt_id', 'status']);
            $table->index(['exam_section_item_id', 'status']);
            $table->unique(['attempt_id', 'exam_section_item_id'], 'writing_submissions_attempt_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_submissions');
    }
};
