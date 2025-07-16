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
        Schema::table('borrowings', function (Blueprint $table) {
             $table->enum('approval_status', ['approved', 'pending', 'rejected'])->nullable()->after('is_returned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
