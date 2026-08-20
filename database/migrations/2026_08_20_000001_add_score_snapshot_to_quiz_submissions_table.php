<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot the quiz's total points and pass mark at grading time, so a
     * teacher editing a quiz's questions/points later doesn't silently
     * change the score or pass/fail outcome of attempts already submitted.
     */
    public function up(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_points')->nullable()->after('essay_score');
            $table->unsignedTinyInteger('pass_mark')->nullable()->after('total_points');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropColumn(['total_points', 'pass_mark']);
        });
    }
};
