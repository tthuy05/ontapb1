<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->constrained()->restrictOnDelete();
            $table->string('title', 180);
            $table->string('slug', 180)->unique();
            $table->text('objectives');
            $table->text('prerequisites')->nullable();
            $table->longText('body');
            $table->json('examples')->nullable();
            $table->text('common_mistakes')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('source_type', 24);
            $table->text('source_reference')->nullable();
            $table->string('license_name', 120)->nullable();
            $table->text('license_url')->nullable();
            $table->text('source_notes')->nullable();
            $table->string('status', 16)->default('draft');
            $table->timestamps();

            $table->index(['topic_id', 'status', 'position']);
            $table->index(['source_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_lessons');
    }
};
