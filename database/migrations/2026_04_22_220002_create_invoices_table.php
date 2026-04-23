<?php

// BIL-06: VAT-compliant invoices for SA/UAE users

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();                  // format: INV-2026-000001
            $table->decimal('amount', 8, 2);                            // subtotal before VAT
            $table->decimal('vat_rate', 5, 2)->default(0.00);           // 15.00 for SA, 5.00 for UAE
            $table->decimal('vat_amount', 8, 2)->default(0.00);
            $table->decimal('total_amount', 8, 2);
            $table->string('currency', 3)->default('SAR');
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->string('payment_gateway')->nullable();               // 'moyasar' or 'tap'
            $table->string('gateway_payment_id')->nullable();
            $table->unsignedInteger('tokens_purchased');
            $table->string('country', 2)->nullable();                   // billing country
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'invoice_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
