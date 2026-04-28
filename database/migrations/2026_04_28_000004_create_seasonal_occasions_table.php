<?php

// SE-01: migrate 26 Gulf/Islamic occasions from config to DB for dynamic admin management

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasonal_occasions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->date('date');
            $table->date('end_date')->nullable();
            $table->string('icon')->default('star');
            $table->string('color', 20)->default('#0F6F5C');
            $table->json('countries')->default('[]');
            $table->boolean('featured')->default(false);
            $table->json('hashtags')->default('[]');
            $table->boolean('is_recurring')->default(true);
            $table->enum('type', ['islamic', 'national', 'commercial'])->default('commercial');
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['active', 'date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_occasions');
    }
};
