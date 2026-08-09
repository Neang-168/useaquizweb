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
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->foreignId('class_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('classes')
                ->nullOnDelete();
        });

        // Add the replacement unique index before dropping the old one, since MySQL
        // is using 'teacher_subject_unique' as the supporting index for the
        // teacher_profile_id foreign key and refuses to drop it otherwise.
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->unique(['teacher_profile_id', 'subject_id', 'class_id'], 'teacher_subject_class_unique');
        });

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropUnique('teacher_subject_unique');
        });

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('semester_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->unique(
                ['teacher_profile_id', 'subject_id', 'academic_year_id', 'semester_id', 'shift_id'],
                'teacher_subject_unique'
            );
        });

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropUnique('teacher_subject_class_unique');
            $table->dropConstrainedForeignId('class_id');
        });
    }
};
