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
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnDelete();

            $table->foreignId('faculty_id')
                ->constrained('faculties')
                ->cascadeOnDelete();

            $table->foreignId('degree_id')
                ->constrained('degrees')
                ->cascadeOnDelete();

            $table->foreignId('major_id')
                ->constrained('majors')
                ->cascadeOnDelete();

            $table->foreignId('promotion_id')
                ->constrained('promotions')
                ->cascadeOnDelete();

            $table->foreignId('stage_id')
                ->constrained('stages')
                ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            $table->foreignId('semester_id')
                ->constrained('semesters')
                ->cascadeOnDelete();

            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->cascadeOnDelete();

            $table->date('enrollment_date');

            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
