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
        // Rename table if it exists
        if (Schema::hasTable('weekly_room_loans')) {
            Schema::rename('weekly_room_loans', 'meeting_schedules');
        }

        // Rename column in room_loans table if it exists
        if (Schema::hasColumn('room_loans', 'weekly_room_loan_id')) {
            Schema::table('room_loans', function (Blueprint $table) {
                $table->dropForeign(['weekly_room_loan_id']);
            });
            
            Schema::table('room_loans', function (Blueprint $table) {
                $table->renameColumn('weekly_room_loan_id', 'meeting_schedule_id');
            });
            
            Schema::table('room_loans', function (Blueprint $table) {
                $table->foreign('meeting_schedule_id')
                    ->references('id')
                    ->on('meeting_schedules')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename column back
        if (Schema::hasColumn('room_loans', 'meeting_schedule_id')) {
            Schema::table('room_loans', function (Blueprint $table) {
                $table->dropForeign(['meeting_schedule_id']);
            });
            
            Schema::table('room_loans', function (Blueprint $table) {
                $table->renameColumn('meeting_schedule_id', 'weekly_room_loan_id');
            });
            
            Schema::table('room_loans', function (Blueprint $table) {
                $table->foreign('weekly_room_loan_id')
                    ->references('id')
                    ->on('weekly_room_loans')
                    ->onDelete('cascade');
            });
        }

        // Rename table back
        if (Schema::hasTable('meeting_schedules')) {
            Schema::rename('meeting_schedules', 'weekly_room_loans');
        }
    }
};
