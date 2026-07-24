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
        Schema::create('serials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('chamber_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_session_id')->constrained()->onDelete('cascade');
            $table->date('booking_date');
            
            $table->string('patient_name');
            $table->string('patient_phone');
            
            $table->integer('serial_number');
            $table->enum('status', ['waiting', 'in_chamber', 'completed', 'cancelled'])->default('waiting');
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, failed
            $table->string('payment_reference')->nullable();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serials');
    }
};
