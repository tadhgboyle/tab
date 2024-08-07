<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('orders', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchaser_id');
            $table->foreign('purchaser_id')->references('id')->on('users');
            $table->unsignedBigInteger('cashier_id');
            $table->foreign('cashier_id')->references('id')->on('users');
            $table->integer('total_price');
            $table->tinyInteger('returned')->default(0);
            $table->foreignId('rotation_id')->constrained();
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
