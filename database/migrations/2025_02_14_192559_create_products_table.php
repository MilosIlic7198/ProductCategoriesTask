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
            $table->foreignId('category_id');
            $table->foreignId('department_id');
            $table->foreignId('manufacturer_id');
            $table->bigInteger('upc');
            $table->bigInteger('sku');
            $table->decimal('regular_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();  //Added soft delete column.

            //Adding indexes to the foreign key columns.
            $table->index(['category_id', 'department_id', 'manufacturer_id']);
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
