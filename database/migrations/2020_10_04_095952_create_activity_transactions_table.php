<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
