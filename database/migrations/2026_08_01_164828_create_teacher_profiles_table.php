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
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('employee_code')->unique();

            $table->foreignId('faculty_id')
                ->constrained('faculties')
                ->cascadeOnDelete();

            $table->foreignId('degree_id')
                ->nullable()
                ->constrained('degrees')
                ->nullOnDelete();

            $table->foreignId('major_id')
                ->nullable()
                ->constrained('majors')
                ->nullOnDelete();

            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();

            $table->date('hire_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
