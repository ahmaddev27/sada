<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// WS-01 → WS-05
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('business_type')->nullable();

            // JSON array of ISO country codes: ['sa', 'ae', 'kw', ...]
            $table->json('countries')->nullable();

            // ENUM-like: fos, sa, ae, kw, qa, bh, om
            $table->string('default_dialect', 10)->default('sa');

            $table->string('logo_path')->nullable();

            // WS-05: soft archive — recoverable for 30 days
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
