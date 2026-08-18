<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Records every option a student selected for a multiple_choice answer.
     * submission_answers.selected_option_id only fits a single choice, but
     * question authoring allows more than one correct option (checkboxes,
     * "check every correct answer"), so a multi-select answer needs a
     * proper set, not one FK. true_false still uses selected_option_id
     * directly since it only ever has one answer.
     */
    public function up(): void
    {
        Schema::create('submission_answer_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submission_answer_id')
                ->constrained('submission_answers')
                ->cascadeOnDelete();

            $table->foreignId('question_option_id')
                ->constrained('question_options')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['submission_answer_id', 'question_option_id'], 'answer_option_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_answer_options');
    }
};
