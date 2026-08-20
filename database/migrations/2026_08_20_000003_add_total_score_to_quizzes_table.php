<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The teacher-chosen target total score for the quiz (e.g. 100). When
     * set, each selected question's points are scaled to sum to this value
     * (stored per-quiz on quiz_questions.points). Null means "use the
     * question bank's own points as-is" for backward compatibility.
     */
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_score')->nullable()->after('pass_mark');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('total_score');
        });
    }
};
