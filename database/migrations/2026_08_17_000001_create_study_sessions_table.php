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
        Schema::create('study_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')
                ->constrained('faculties')
                ->cascadeOnDelete();

            $table->foreignId('degree_id')
                ->nullable()
                ->constrained('degrees')
                ->nullOnDelete();

            $table->foreignId('major_id')
                ->nullable()
                ->constrained('majors')
                ->nullOnDelete();

            $table->string('code');
            $table->string('name');
            $table->string('name_kh')->nullable();

            $table->integer('credit');

            $table->text('description')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(['faculty_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_sessions');
    }
};
