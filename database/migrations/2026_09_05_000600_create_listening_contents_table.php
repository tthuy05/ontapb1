<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listening_contents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->longText('transcript');
            $table->string('audio_path', 500);
            $table->string('audio_mime', 100);
            $table->unsignedBigInteger('audio_size_bytes');
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedTinyInteger('speaker_count')->nullable();
            $table->string('accent_notes', 255)->nullable();
            $table->string('cefr_level', 4)->default('B1');
            $table->unsignedTinyInteger('difficulty')->default(2);
            $table->string('source_type', 24);
            $table->text('source_reference')->nullable();
            $table->string('license_name', 120)->nullable();
            $table->text('license_url')->nullable();
            $table->text('source_notes')->nullable();
            $table->string('status', 16)->default('draft');
            $table->timestamps();

            $table->index(['status', 'difficulty']);
            $table->index(['source_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listening_contents');
    }
};
