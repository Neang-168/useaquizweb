<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('faculty_id')
                ->nullable()
                ->after('major_id')
                ->constrained('faculties')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->after('faculty_id')
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('promotion_id')
                ->nullable()
                ->after('department_id')
                ->constrained('promotions')
                ->nullOnDelete();

            $table->foreignId('study_session_id')
                ->nullable()
                ->after('term_id')
                ->constrained('study_sessions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['promotion_id']);
            $table->dropForeign(['study_session_id']);
            $table->dropColumn(['faculty_id', 'department_id', 'promotion_id', 'study_session_id']);
        });
    }
};
