<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('passage_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('listening_content_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('skill', 24);
            $table->string('type', 32);
            $table->longText('prompt');
            $table->longText('explanation')->nullable();
            $table->json('answer_config')->nullable();
            $table->string('cefr_level', 4)->default('B1');
            $table->unsignedTinyInteger('difficulty')->default(2);
            $table->json('metadata')->nullable();
            $table->string('source_type', 24);
            $table->text('source_reference')->nullable();
            $table->string('license_name', 120)->nullable();
            $table->text('license_url')->nullable();
            $table->text('source_notes')->nullable();
            $table->string('status', 16)->default('draft');
            $table->timestamps();

            $table->index(['skill', 'type']);
            $table->index(['skill', 'status', 'difficulty']);
            $table->index(['source_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
