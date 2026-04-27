<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE posts MODIFY COLUMN platform ENUM('instagram','facebook','tiktok','snapchat','x','linkedin') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE posts MODIFY COLUMN platform ENUM('instagram','facebook','tiktok','snapchat','x') NOT NULL");
        }
    }
};
