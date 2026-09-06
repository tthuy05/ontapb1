<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_prompts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('task_type', 32);
            $table->string('title', 200);
            $table->longText('instructions');
            $table->unsignedSmallInteger('minimum_words')->nullable();
            $table->unsignedSmallInteger('recommended_minutes')->nullable();
            $table->longText('guidance')->nullable();
            $table->json('checklist')->nullable();
            $table->longText('model_answer')->nullable();
            $table->string('source_type', 24);
            $table->text('source_reference')->nullable();
            $table->string('license_name', 120)->nullable();
            $table->text('license_url')->nullable();
            $table->text('source_notes')->nullable();
            $table->string('status', 16)->default('draft');
            $table->timestamps();

            $table->index(['topic_id', 'status']);
            $table->index(['source_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_prompts');
    }
};
