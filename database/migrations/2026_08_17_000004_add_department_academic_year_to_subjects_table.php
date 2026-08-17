<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('faculty_id')
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('major_id')
                ->constrained('academic_years')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn(['department_id', 'academic_year_id']);
        });
    }
};
