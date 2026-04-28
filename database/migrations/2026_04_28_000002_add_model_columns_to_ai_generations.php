<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->string('provider', 30)->nullable()->after('agent_type');
            $table->string('ai_model', 80)->nullable()->after('provider');
            $table->decimal('cost_usd', 10, 8)->default(0)->after('sada_tokens_charged');

            $table->index('provider');
            $table->index('ai_model');
        });
    }

    public function down(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->dropIndex(['provider']);
            $table->dropIndex(['ai_model']);
            $table->dropColumn(['provider', 'ai_model', 'cost_usd']);
        });
    }
};
