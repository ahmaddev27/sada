<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BI-01 → BI-04
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->unique()->constrained()->cascadeOnDelete();

            $table->text('description')->nullable();
            $table->string('tone')->nullable();
            $table->text('target_audience')->nullable();

            // BI-03: max 10 banned words
            $table->json('banned_words')->nullable();

            // BI-04: max 5 example posts
            $table->json('example_posts')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_identities');
    }
};
