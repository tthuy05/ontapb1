<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabulary_review_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vocabulary_progress_id')
                ->unique()
                ->constrained('vocabulary_progress')
                ->cascadeOnDelete();
            $table->timestamp('due_at')->index();
            $table->unsignedSmallInteger('interval_days')->default(0);
            $table->unsignedInteger('streak')->default(0);
            $table->unsignedInteger('lapses')->default(0);
            $table->string('last_rating', 8)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabulary_review_schedules');
    }
};
