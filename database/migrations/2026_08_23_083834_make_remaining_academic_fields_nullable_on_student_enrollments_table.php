<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = ['faculty_id', 'degree_id', 'major_id', 'stage_id', 'academic_year_id', 'shift_id'];

    private array $tables = [
        'faculty_id' => 'faculties',
        'degree_id' => 'degrees',
        'major_id' => 'majors',
        'stage_id' => 'stages',
        'academic_year_id' => 'academic_years',
        'shift_id' => 'shifts',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->columns as $column) {
            Schema::table('student_enrollments', function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
            });

            Schema::table('student_enrollments', function (Blueprint $table) use ($column) {
                $table->foreignId($column)->nullable()->change();

                $table->foreign($column)
                    ->references('id')->on($this->tables[$column])
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->columns as $column) {
            Schema::table('student_enrollments', function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
            });

            Schema::table('student_enrollments', function (Blueprint $table) use ($column) {
                $table->foreignId($column)->nullable(false)->change();

                $table->foreign($column)
                    ->references('id')->on($this->tables[$column])
                    ->cascadeOnDelete();
            });
        }
    }
};
