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
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->foreignId('promotion_id')
                ->nullable()
                ->change();

            $table->foreign('promotion_id')
                ->references('id')->on('promotions')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->foreignId('promotion_id')
                ->nullable(false)
                ->change();

            $table->foreign('promotion_id')
                ->references('id')->on('promotions')
                ->cascadeOnDelete();
        });
    }
};
