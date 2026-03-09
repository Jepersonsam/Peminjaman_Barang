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
        Schema::table('items', function (Blueprint $table) {
            // Drop old category column if exists
            if (Schema::hasColumn('items', 'category')) {
                $table->dropColumn('category');
            }
            
            // Add category_id foreign key
            $table->foreignId('category_id')->nullable()->after('name')->constrained('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            
            // Restore category column if needed
            $table->string('category', 100)->nullable()->after('name');
        });
    }
};
