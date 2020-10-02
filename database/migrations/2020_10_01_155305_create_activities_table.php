<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->integer('slots');
            $table->float('price');
            $table->boolean('pst')->default(false);
            $table->boolean('deleted')->default(false);
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->boolean('all_day')->default(false);
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
