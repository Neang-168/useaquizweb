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
        DB::statement("ALTER TABLE questions MODIFY type ENUM('multiple_choice', 'true_false', 'matching') NOT NULL");

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('sample_answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('sample_answer')->nullable();
        });

        DB::statement("ALTER TABLE questions MODIFY type ENUM('multiple_choice', 'true_false', 'essay', 'matching') NOT NULL");
    }
};
