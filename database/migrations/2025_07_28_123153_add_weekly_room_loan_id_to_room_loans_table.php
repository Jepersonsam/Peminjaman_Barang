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
        Schema::table('room_loans', function (Blueprint $table) {
            $table->foreignId('meeting_schedule_id')->nullable()
                ->constrained('meeting_schedules')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_loans', function (Blueprint $table) {
            $table->dropForeign(['meeting_schedule_id']);
            $table->dropColumn('meeting_schedule_id');
        });
    }
};
