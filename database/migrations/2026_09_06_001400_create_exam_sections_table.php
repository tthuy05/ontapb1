<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('skill', 24);
            $table->string('title', 160);
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('position');
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->string('navigation_mode', 24)->default('free_within_section');
            $table->timestamps();

            $table->unique(['exam_id', 'position']);
            $table->index(['exam_id', 'skill']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sections');
    }
};
