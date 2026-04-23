<?php

// ADS-01→ADS-11

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name', 120);

            $table->enum('objective', [
                'awareness',
                'traffic',
                'engagement',
                'conversions',
                'app_installs',
                'video_views',
            ]);

            $table->enum('platform', ['instagram', 'facebook']); // Meta only — MVP

            $table->enum('status', [
                'draft',
                'pending',
                'active',
                'paused',
                'completed',
                'rejected',
            ])->default('draft');

            // Targeting
            $table->json('target_countries')->nullable();
            $table->unsignedTinyInteger('target_age_min')->default(18);
            $table->unsignedTinyInteger('target_age_max')->default(65);
            $table->enum('target_gender', ['all', 'male', 'female'])->default('all');
            $table->json('target_interests')->nullable();

            // Budget
            $table->enum('budget_type', ['daily', 'lifetime']);
            $table->decimal('budget_amount', 10, 2);
            $table->string('budget_currency', 3)->default('SAR');

            // Schedule
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            // Meta API references — set after successful submission (ADS-07)
            $table->string('meta_campaign_id')->nullable();
            $table->string('meta_adset_id')->nullable();
            $table->string('meta_ad_id')->nullable();

            // ANL-01: insights snapshot {spend, reach, impressions, clicks, ctr, cpc, roas}
            $table->json('insights')->nullable();
            $table->timestamp('insights_synced_at')->nullable();

            $table->timestamps();

            $table->index('workspace_id');
            $table->index('status');
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
