<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A per-quiz override of a question's points, so a teacher can set a
     * quiz's Total Score and have each question's weight scaled to hit it,
     * without touching the question's own points in the shared bank (which
     * other quizzes may also be using).
     */
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->unsignedSmallInteger('points')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
};
