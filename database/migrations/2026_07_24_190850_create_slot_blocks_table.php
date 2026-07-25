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
        Schema::create('slot_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('chamber_id')->nullable();
            $table->date('block_date');
            $table->string('reason')->nullable();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign(['tenant_id', 'doctor_id'])->references(['tenant_id', 'id'])->on('doctors')->onDelete('cascade');
            $table->foreign(['tenant_id', 'chamber_id'])->references(['tenant_id', 'id'])->on('chambers')->onDelete('cascade');
            $table->unique(['tenant_id', 'id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_blocks');
    }
};
