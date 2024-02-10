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
    public function up(): void
    {
        Schema::create('activities', static function (Blueprint $table) {
            $table->id();
            $table->string('name', 36);
            $table->string('location', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('unlimited_slots');
            $table->integer('slots');
            $table->bigInteger('price');
            $table->boolean('pst');
            $table->foreignId('category_id')->constrained();
            $table->dateTime('start');
            $table->dateTime('end');
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
        Schema::dropIfExists('activities');
    }
}
