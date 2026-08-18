<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Records, for a matching-type answer, which right-hand pair the
     * student paired with each left-hand pair. left_pair_id and
     * selected_right_pair_id both point at question_matching_pairs rows
     * (of the same question); the answer for that left item is correct
     * when they're equal. submission_answers has no column shaped for
     * this at all today — matching questions currently can't be answered
     * or graded.
     */
    public function up(): void
    {
        Schema::create('submission_answer_matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submission_answer_id')
                ->constrained('submission_answers')
                ->cascadeOnDelete();

            $table->foreignId('left_pair_id')
                ->constrained('question_matching_pairs')
                ->cascadeOnDelete();

            $table->foreignId('selected_right_pair_id')
                ->nullable()
                ->constrained('question_matching_pairs')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['submission_answer_id', 'left_pair_id'], 'answer_match_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_answer_matches');
    }
};
