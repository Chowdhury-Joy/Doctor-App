<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serials', function (Blueprint $table) {
            $table->dropForeign(['tenant_id', 'schedule_session_id']);
            $table->dropForeign(['tenant_id', 'doctor_id']);
            $table->dropForeign(['tenant_id', 'chamber_id']);
            $table->dropUnique('serials_session_date_number_unique');
        });

        Schema::table('serials', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
            $table->unsignedBigInteger('chamber_id')->nullable()->change();
            $table->renameColumn('schedule_session_id', 'bookable_id');
        });

        Schema::table('serials', function (Blueprint $table) {
            $table->string('bookable_type')->nullable()->after('bookable_id');
        });

        DB::table('serials')->update(['bookable_type' => 'App\\\\Models\\\\ScheduleSession']);

        Schema::table('serials', function (Blueprint $table) {
            $table->string('bookable_type')->nullable(false)->change();
            
            $table->foreign(['tenant_id', 'doctor_id'])->references(['tenant_id', 'id'])->on('doctors')->onDelete('cascade');
            $table->foreign(['tenant_id', 'chamber_id'])->references(['tenant_id', 'id'])->on('chambers')->onDelete('cascade');
            
            $table->unique(
                ['tenant_id', 'bookable_type', 'bookable_id', 'booking_date', 'serial_number'],
                'serials_bookable_date_number_unique'
            );
        });
    }

    public function down(): void
    {
        // 
    }
};
