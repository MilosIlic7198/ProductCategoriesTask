<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_number')->unique(); //Unique makes it indexed.
            $table->foreignId('category_id')->constrained()->onDelete('set null');
            $table->foreignId('department_id')->constrained()->onDelete('set null');
            $table->foreignId('manufacturer_id')->constrained()->onDelete('set null');
            $table->string('upc');
            $table->string('sku');
            $table->decimal('regular_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();  //Added soft delete column.

            //Adding indexes to the foreign key columns.
            $table->index('category_id');
            $table->index('department_id');
            $table->index('manufacturer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
