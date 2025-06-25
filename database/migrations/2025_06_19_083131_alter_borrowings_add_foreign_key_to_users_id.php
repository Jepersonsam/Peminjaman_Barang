<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            // Pastikan kolom users_id sudah bertipe BIGINT
            $table->unsignedBigInteger('users_id')->change();

            // Tambahkan foreign key constraint
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropForeign(['users_id']);
        });
    }
};
