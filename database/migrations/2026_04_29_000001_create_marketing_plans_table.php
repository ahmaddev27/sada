<?php

// MKT-01: marketing plan generator — stores AI-generated plans per workspace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->json('inputs');
            $table->json('plan')->nullable();
            $table->enum('status', ['generating', 'completed', 'failed'])->default('generating');
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->decimal('cost_usd', 12, 8)->nullable();
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_plans');
    }
};
