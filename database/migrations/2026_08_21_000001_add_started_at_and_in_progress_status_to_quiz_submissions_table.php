<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks when a student actually started a quiz attempt, so the
     * countdown timer can be computed server-side (started_at + duration)
     * instead of trusting a client-side clock that resets on refresh.
     * Adds an "in_progress" status for the attempt row that exists between
     * starting and submitting/expiring.
     */
    public function up(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('attempt_number');
        });

        DB::statement("ALTER TABLE quiz_submissions MODIFY status ENUM('in_progress', 'submitted', 'graded') NOT NULL DEFAULT 'submitted'");
    }

    public function down(): void
    {
        DB::statement("UPDATE quiz_submissions SET status = 'submitted' WHERE status = 'in_progress'");
        DB::statement("ALTER TABLE quiz_submissions MODIFY status ENUM('submitted', 'graded') NOT NULL DEFAULT 'submitted'");

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
    }
};
