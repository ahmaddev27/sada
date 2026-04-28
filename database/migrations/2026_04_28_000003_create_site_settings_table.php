<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string|text|url|email|phone|bool|image
            $table->string('group', 30)->default('general'); // general|branding|contact|seo|social
            $table->string('label_ar', 100);
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'sort_order']);
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
