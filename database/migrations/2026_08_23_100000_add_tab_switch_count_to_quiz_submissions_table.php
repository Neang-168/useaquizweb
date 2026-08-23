<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks how many times a student left the quiz tab/window during an
     * attempt (reported by the client on submit), so teachers can review
     * potential integrity issues in the score report.
     */
    public function up(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->unsignedInteger('tab_switch_count')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropColumn('tab_switch_count');
        });
    }
};
