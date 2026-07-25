<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->uuid('serial_id'); // foreign to serials.id
            $table->string('gateway'); // bkash, nagad, sslcommerz
            $table->string('transaction_id')->nullable();
            $table->json('webhook_payload')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign(['tenant_id', 'serial_id'])->references(['tenant_id', 'id'])->on('serials')->onDelete('cascade');
            
            // Add unique constraint for idempotency
            $table->unique(['gateway', 'transaction_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
