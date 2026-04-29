<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // dialect/platform/content_type/prompt only apply to content_generator agent type.
    // Other agents (marketing_plan, etc.) don't have these dimensions.
    public function up(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->string('dialect', 10)->nullable()->default(null)->change();
            $table->string('platform', 30)->nullable()->default(null)->change();
            $table->string('content_type', 30)->nullable()->default(null)->change();
            $table->text('prompt')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->string('dialect', 10)->nullable(false)->change();
            $table->string('platform', 30)->nullable(false)->change();
            $table->string('content_type', 30)->nullable(false)->change();
            $table->text('prompt')->nullable(false)->change();
        });
    }
};
