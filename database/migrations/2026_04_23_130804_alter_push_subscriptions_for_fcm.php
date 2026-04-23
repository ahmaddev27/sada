<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique('push_sub_user_endpoint_unique');
            $table->dropColumn(['endpoint', 'endpoint_hash', 'p256dh_key', 'auth_key']);
            $table->string('fcm_token')->after('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'fcm_token'], 'push_sub_user_token_unique');
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique('push_sub_user_token_unique');
            $table->dropColumn('fcm_token');
            $table->text('endpoint')->after('user_id');
            $table->string('endpoint_hash', 64)->after('endpoint');
            $table->string('p256dh_key')->after('endpoint_hash');
            $table->string('auth_key')->after('p256dh_key');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'endpoint_hash'], 'push_sub_user_endpoint_unique');
        });
    }
};
