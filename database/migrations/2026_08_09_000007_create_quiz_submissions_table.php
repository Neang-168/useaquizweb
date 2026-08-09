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
        Schema::create('quiz_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnDelete();

            $table->unsignedInteger('mcq_score')->default(0);
            $table->unsignedInteger('essay_score')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['submitted', 'graded'])->default('submitted');

            $table->timestamps();

            $table->unique(['quiz_id', 'student_profile_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_submissions');
    }
};
