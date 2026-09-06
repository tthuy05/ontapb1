<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabularies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->constrained()->restrictOnDelete();
            $table->string('term', 160);
            $table->string('part_of_speech', 40)->nullable();
            $table->string('phonetic', 120)->nullable();
            $table->text('definition');
            $table->text('translation')->nullable();
            $table->text('example_sentence')->nullable();
            $table->text('notes')->nullable();
            $table->string('pronunciation_audio_path', 500)->nullable();
            $table->string('source_type', 24);
            $table->text('source_reference')->nullable();
            $table->string('license_name', 120)->nullable();
            $table->text('license_url')->nullable();
            $table->text('source_notes')->nullable();
            $table->string('status', 16)->default('draft');
            $table->timestamps();

            $table->unique(['topic_id', 'term', 'part_of_speech'], 'vocabularies_topic_term_pos_unique');
            $table->index(['status', 'topic_id']);
            $table->index(['source_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabularies');
    }
};
