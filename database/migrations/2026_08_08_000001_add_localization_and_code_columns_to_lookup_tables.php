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
        Schema::table('faculties', function (Blueprint $table) {
            $table->string('name_kh')->nullable()->after('name');
        });

        Schema::table('degrees', function (Blueprint $table) {
            $table->string('name_kh')->nullable()->after('name');
            $table->unsignedInteger('duration_years')->nullable()->after('description');
        });

        Schema::table('majors', function (Blueprint $table) {
            $table->string('name_kh')->nullable()->after('name');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->string('name_kh')->nullable()->after('name');
        });

        Schema::table('academic_years', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
            $table->string('name_kh')->nullable()->after('name');
            $table->boolean('is_current')->default(false)->after('status');
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
            $table->string('name_kh')->nullable()->after('name');
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
            $table->string('name_kh')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculties', function (Blueprint $table) {
            $table->dropColumn('name_kh');
        });

        Schema::table('degrees', function (Blueprint $table) {
            $table->dropColumn(['name_kh', 'duration_years']);
        });

        Schema::table('majors', function (Blueprint $table) {
            $table->dropColumn('name_kh');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('name_kh');
        });

        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn(['code', 'name_kh', 'is_current']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['code', 'name_kh']);
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn(['code', 'name_kh']);
        });
    }
};
