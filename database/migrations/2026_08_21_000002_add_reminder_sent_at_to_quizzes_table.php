<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->timestamp('start_reminder_sent_at')->nullable()->after('end_at');
            $table->timestamp('end_reminder_sent_at')->nullable()->after('start_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['start_reminder_sent_at', 'end_reminder_sent_at']);
        });
    }
};
