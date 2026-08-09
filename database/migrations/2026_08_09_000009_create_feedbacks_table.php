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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_profile_id')
                ->constrained('teacher_profiles')
                ->cascadeOnDelete();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnDelete();

            $table->foreignId('quiz_submission_id')
                ->nullable()
                ->constrained('quiz_submissions')
                ->cascadeOnDelete();

            $table->text('message');
            $table->dateTime('sent_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
