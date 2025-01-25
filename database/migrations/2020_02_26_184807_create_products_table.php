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
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('price');
            $table->foreignId('category_id')->constrained();
            $table->boolean('pst')->default(false);
            $table->integer('stock');
            // true/false if this product has unlimited stock (since -1 could be a valid inventory count)
            // This will override any stock count
            $table->boolean('unlimited_stock');
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
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}
