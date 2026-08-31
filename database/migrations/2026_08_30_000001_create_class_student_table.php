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
        Schema::create('class_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')
                ->constrained('classes')
                ->cascadeOnDelete();
            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnDelete();
            $table->string('status')->default('Active');
            $table->timestamps();

            $table->unique(['class_id', 'student_profile_id']);
        });

        // Carry each student's existing single class assignment (their
        // latest enrollment's class_id) into the new many-to-many pivot, so
        // nobody loses their current class when this ships.
        DB::table('student_enrollments')
            ->whereNotNull('class_id')
            ->orderBy('enrollment_date')
            ->get(['student_profile_id', 'class_id', 'status'])
            ->groupBy('student_profile_id')
            ->each(function ($rows, $studentProfileId) {
                $latest = $rows->last();
                $now = now();

                DB::table('class_student')->insertOrIgnore([
                    'class_id' => $latest->class_id,
                    'student_profile_id' => $studentProfileId,
                    'status' => $latest->status ?: 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_student');
    }
};
