<?php

// CON-01, CON-02, CON-06, CON-11

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();

            // CON-01, CON-02, CON-03, CON-04, CON-05
            $table->enum('provider', ['instagram', 'facebook', 'tiktok', 'snapchat', 'x']);

            // Provider-side identifiers
            $table->string('provider_account_id');
            $table->string('account_name');
            $table->string('account_picture_url')->nullable();

            // CON-06: Encrypted at rest (AES-256 via Laravel encrypted cast)
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            // CON-09: Connection health status
            $table->enum('status', ['healthy', 'expired', 'revoked', 'error'])->default('healthy');

            // Granted OAuth scopes + provider-specific extras
            $table->json('scopes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // CON-11: multiple accounts per platform allowed; same provider ID once per workspace
            $table->unique(['workspace_id', 'provider', 'provider_account_id'], 'social_accounts_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
