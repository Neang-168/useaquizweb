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
        Schema::create('llos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clo_id')
                ->constrained('clos')
                ->cascadeOnDelete();

            $table->string('code', 50);
            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('bloom_level', ['Remember', 'Understand', 'Apply', 'Analyze', 'Evaluate', 'Create'])
                ->default('Understand');

            $table->unsignedSmallInteger('lesson_no')->nullable();

            $table->boolean('status')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['clo_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llos');
    }
};
