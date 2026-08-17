<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('faculty_id')
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('term_id')
                ->nullable()
                ->after('semester_id')
                ->constrained('terms')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['term_id']);
            $table->dropColumn(['department_id', 'term_id']);
        });
    }
};
