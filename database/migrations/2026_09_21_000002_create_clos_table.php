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
        Schema::create('clos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plo_id')
                ->constrained('plos')
                ->restrictOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->string('code', 50);
            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('bloom_level', ['Remember', 'Understand', 'Apply', 'Analyze', 'Evaluate', 'Create'])
                ->default('Understand');

            $table->boolean('status')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['subject_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clos');
    }
};
