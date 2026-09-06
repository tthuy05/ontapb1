<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->string('skill', 24);
            $table->text('instructions')->nullable();
            $table->unsignedTinyInteger('difficulty')->default(2);
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status', 16)->default('draft');
            $table->timestamps();

            $table->index(['skill', 'status', 'difficulty']);
            $table->index(['status', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
