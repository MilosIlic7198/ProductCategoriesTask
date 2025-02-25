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
        Schema::table('products', function (Blueprint $table) {
            //Add foreign key constraints for the category_id, department_id, and manufacturer_id columns.
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //Drop the foreign key constraints.
            $table->dropForeign(['category_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['manufacturer_id']);
        });
    }
};
