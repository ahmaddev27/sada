<?php

// ANL-01→ANL-07

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->nullOnDelete()->constrained();
            $table->foreignId('campaign_id')->nullable()->nullOnDelete()->constrained();
            $table->enum('platform', ['instagram', 'facebook', 'tiktok', 'snapchat', 'x']);
            $table->date('snapshot_date');

            // ANL-01 KPI columns
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->unsignedInteger('saves')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->decimal('spend', 10, 2)->default(0.00); // for paid ads (ROAS later)
            $table->unsignedInteger('follower_count')->nullable(); // daily follower snapshot

            $table->timestamps();

            // Prevent duplicate snapshots for same record/day
            $table->unique(
                ['workspace_id', 'post_id', 'campaign_id', 'platform', 'snapshot_date'],
                'analytics_snapshots_unique'
            );

            // Query performance indexes
            $table->index('workspace_id');
            $table->index('snapshot_date');
            $table->index('platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_snapshots');
    }
};
