<?php

// SE-05: content templates per occasion — admin-managed, platform-specific

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasonal_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seasonal_occasion_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['instagram', 'facebook', 'tiktok', 'snapchat', 'x', 'all'])->default('all');
            $table->text('content_template');
            $table->json('hashtags')->default('[]');
            $table->string('tone')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['seasonal_occasion_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_templates');
    }
};
