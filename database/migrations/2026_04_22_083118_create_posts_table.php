<?php

// CG-08, SCH-01

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->text('content');
            $table->json('hashtags')->nullable();

            $table->enum('platform', ['instagram', 'facebook', 'tiktok', 'snapchat', 'x']);
            $table->enum('content_type', ['post', 'reel', 'story', 'ad', 'thread', 'snap_caption']);
            $table->string('dialect', 10)->default('fos');

            $table->enum('status', ['draft', 'scheduled', 'published', 'failed'])->default('draft');
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->foreignId('social_account_id')->nullable()->constrained()->nullOnDelete();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
