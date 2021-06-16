<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_limits', function (Blueprint $table) {
            $table->bigIncrements('limit_id');
            $table->integer('user_id');
            $table->integer('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->float('limit_per');
            $table->integer('duration');
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
        Schema::dropIfExists('user_limits');
    }
}
