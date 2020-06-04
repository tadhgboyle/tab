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
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->float('price');
            $table->string('category');
            $table->tinyInteger('pst')->default('0');

            $table->integer('stock');
            // true/false if this product has unlimited stock (since -1 could be a valid inventory count)
            // This will override any stock count
            $table->boolean('unlimited_stock');
            $table->integer('box_size');
            // Allow to bypass the stock count
            $table->boolean('stock_override');

            $table->integer('creator_id');
            $table->integer('editor_id')->nullable();
            $table->timestamps();
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
