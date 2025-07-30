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
            $table->foreignId('weekly_room_loan_id')->nullable()
                ->constrained('weekly_room_loans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_loans', function (Blueprint $table) {
            $table->dropForeign(['weekly_room_loan_id']);
            $table->dropColumn('weekly_room_loan_id');
        });
    }
};
