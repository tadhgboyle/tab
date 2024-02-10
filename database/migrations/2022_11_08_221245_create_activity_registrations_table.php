<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('cashier_id');
            $table->foreign('cashier_id')->references('id')->on('users');
            $table->foreignId('activity_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->bigInteger('activity_price');
            $table->float('activity_gst');
            $table->float('activity_pst')->nullable();
            $table->bigInteger('total_price');
            $table->boolean('returned')->default(false);
            $table->foreignId('rotation_id')->constrained();
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
        Schema::dropIfExists('activity_registrations');
    }
};
