<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_section_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('writing_prompt_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('speaking_prompt_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            $table->decimal('points', 6, 2)->default(1.00);
            $table->timestamps();

            $table->unique(['exam_section_id', 'position']);
            $table->index(['exam_section_id', 'question_id'], 'exam_section_items_section_question_index');
            $table->index(['exam_section_id', 'writing_prompt_id'], 'exam_section_items_section_writing_index');
            $table->index(['exam_section_id', 'speaking_prompt_id'], 'exam_section_items_section_speaking_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_section_items');
    }
};
