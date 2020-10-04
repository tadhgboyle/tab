<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('cashier_id');
            $table->integer('activity_id');
            $table->float('activity_price');
            $table->float('activity_gst');
            $table->boolean('status')->default(false);
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
        Schema::dropIfExists('activity_transactions');
    }
}
