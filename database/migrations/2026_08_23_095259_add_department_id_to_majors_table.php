<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('majors', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('degree_id')
                ->constrained('departments')
                ->cascadeOnDelete();
        });

        Schema::table('majors', function (Blueprint $table) {
            $table->dropForeign(['degree_id']);
        });

        Schema::table('majors', function (Blueprint $table) {
            $table->foreignId('degree_id')->nullable()->change();

            $table->foreign('degree_id')
                ->references('id')->on('degrees')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('majors', function (Blueprint $table) {
            $table->dropForeign(['degree_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('majors', function (Blueprint $table) {
            $table->foreignId('degree_id')->nullable(false)->change();

            $table->foreign('degree_id')
                ->references('id')->on('degrees')
                ->cascadeOnDelete();
        });
    }
};
