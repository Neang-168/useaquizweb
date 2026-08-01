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
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('degree_id')
                ->constrained('degrees')
                ->cascadeOnDelete();

            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['degree_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majors');
    }
};
