<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->unsignedInteger('max_attempts')->default(1)->after('total_questions');
            $table->boolean('shuffle_questions')->default(false)->after('max_attempts');
            $table->boolean('shuffle_options')->default(false)->after('shuffle_questions');
            $table->unsignedTinyInteger('pass_mark')->nullable()->after('shuffle_options');
            $table->dateTime('start_at')->nullable()->after('pass_mark');
            $table->dateTime('end_at')->nullable()->after('start_at');
        });

        // Remap any existing lifecycle values before narrowing the enum, so the
        // ALTER below never truncates a row into an invalid empty status.
        DB::table('quizzes')->where('status', 'Upcoming')->update(['status' => 'Draft']);
        DB::table('quizzes')->where('status', 'Active')->update(['status' => 'Published']);
        DB::table('quizzes')->where('status', 'Completed')->update(['status' => 'Closed']);

        DB::statement("ALTER TABLE quizzes MODIFY status ENUM('Draft', 'Published', 'Closed') NOT NULL DEFAULT 'Draft'");

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->date('start_date')->nullable();
        });

        DB::table('quizzes')->where('status', 'Draft')->update(['status' => 'Upcoming']);
        DB::table('quizzes')->where('status', 'Published')->update(['status' => 'Active']);
        DB::table('quizzes')->where('status', 'Closed')->update(['status' => 'Completed']);

        DB::statement("ALTER TABLE quizzes MODIFY status ENUM('Upcoming', 'Active', 'Completed') NOT NULL DEFAULT 'Upcoming'");

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'max_attempts',
                'shuffle_questions',
                'shuffle_options',
                'pass_mark',
                'start_at',
                'end_at',
            ]);
        });
    }
};
