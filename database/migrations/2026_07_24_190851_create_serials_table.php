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
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('chamber_id');
            $table->unsignedBigInteger('schedule_session_id');
            $table->date('booking_date');
            
            $table->string('patient_name');
            $table->string('patient_phone');
            
            $table->integer('serial_number');
            $table->enum('status', ['waiting', 'in_chamber', 'completed', 'cancelled'])->default('waiting');
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, failed
            $table->string('payment_reference')->nullable();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign(['tenant_id', 'doctor_id'])->references(['tenant_id', 'id'])->on('doctors')->onDelete('cascade');
            $table->foreign(['tenant_id', 'chamber_id'])->references(['tenant_id', 'id'])->on('chambers')->onDelete('cascade');
            $table->foreign(['tenant_id', 'schedule_session_id'])->references(['tenant_id', 'id'])->on('schedule_sessions')->onDelete('cascade');
            $table->unique(['tenant_id', 'id']);

            // Backstop against two patients being issued the same queue number for
            // the same session on the same day (online booking vs. walk-in race).
            $table->unique(
                ['tenant_id', 'schedule_session_id', 'booking_date', 'serial_number'],
                'serials_session_date_number_unique'
            );
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
