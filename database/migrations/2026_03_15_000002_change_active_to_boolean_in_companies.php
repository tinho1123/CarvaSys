<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE companies MODIFY active TINYINT(1) NOT NULL DEFAULT 1');
        DB::statement('UPDATE companies SET active = 1 WHERE active = 0');
    }

    public function down(): void
    {
        DB::statement("UPDATE companies SET active = 'Y' WHERE active = 1");
        DB::statement("UPDATE companies SET active = 'N' WHERE active = 0");
        DB::statement("ALTER TABLE companies MODIFY active ENUM('Y','N') NOT NULL");
    }
};
