<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE faculties MODIFY code VARCHAR(50) NULL');
        DB::statement('ALTER TABLE departments MODIFY code VARCHAR(50) NULL');
        DB::statement('ALTER TABLE majors MODIFY code VARCHAR(50) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE faculties MODIFY code VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE departments MODIFY code VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE majors MODIFY code VARCHAR(50) NOT NULL');
    }
};
