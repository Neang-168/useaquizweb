<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('subject_materials');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('subject_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->foreignId('teacher_profile_id')
                ->constrained('teacher_profiles')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('duration_label')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();

            $table->timestamps();
        });
    }
};
