<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 36);
            $table->string('location', 36)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('unlimited_slots')->default(false);
            $table->integer('slots');
            $table->float('price');
            $table->float('pst');
            $table->integer('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->boolean('deleted')->default(false);
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
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
        Schema::dropIfExists('activities');
    }
}
