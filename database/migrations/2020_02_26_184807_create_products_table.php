<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('name')->unique();
            $table->float('price');
            $table->integer('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->boolean('pst')->default(false);
            $table->integer('stock');
            // true/false if this product has unlimited stock (since -1 could be a valid inventory count)
            // This will override any stock count
            $table->boolean('unlimited_stock');
            $table->integer('box_size');
            // Allow to bypass the stock count
            $table->boolean('stock_override');
            $table->timestamps();
            $table->softDeletes();
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
