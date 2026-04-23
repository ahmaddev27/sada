<?php

// BIL-02: token deductions and credits per action

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['purchase', 'deduction', 'refund', 'bonus', 'expiry']);
            $table->integer('amount');                                   // positive=credit, negative=deduction
            $table->unsignedInteger('balance_after');
            $table->string('description');                               // Arabic description of the action
            $table->string('reference_type')->nullable();                // 'payment', 'post', 'ai_generation', 'campaign'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();                        // payment gateway data, invoice info
            $table->timestamps();

            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_transactions');
    }
};
