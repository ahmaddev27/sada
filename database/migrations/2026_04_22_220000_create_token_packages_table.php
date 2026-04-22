<?php

// BIL-01: token packages available for purchase

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');                                     // Arabic name e.g. "باقة المبتدئ"
            $table->string('name_en');                                  // English name
            $table->unsignedInteger('tokens');                          // Token count in package
            $table->decimal('price', 8, 2);                             // Price in SAR
            $table->string('currency', 3)->default('SAR');
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_packages');
    }
};
