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
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->unsignedInteger('attempt_number')->default(1)->after('student_profile_id');
        });

        // The old composite unique (quiz_id, student_profile_id) is the only
        // index covering quiz_id, so it's backing that column's foreign key.
        // A plain index on quiz_id has to exist before it can be dropped.
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->index('quiz_id');
        });

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropUnique(['quiz_id', 'student_profile_id']);
            $table->unique(['quiz_id', 'student_profile_id', 'attempt_number'], 'quiz_submissions_attempt_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropUnique('quiz_submissions_attempt_unique');
            $table->unique(['quiz_id', 'student_profile_id']);
        });

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropIndex(['quiz_id']);
        });

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropColumn('attempt_number');
        });
    }
};
