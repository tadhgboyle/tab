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
            $table->boolean('unlimited_slots')->default(false);
            $table->integer('slots');
            $table->string('attendees', 1024)->default('[]');
            $table->float('price');
            $table->boolean('pst')->default(false);
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
