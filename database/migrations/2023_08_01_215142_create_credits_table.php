<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('transaction_id')->constrained();
            $table->integer('amount');
            $table->integer('amount_used')->default(0);
            $table->string('reason')->default('GIFT_CARD_RETURN'); // TODO allow issuing credits by a staff member
            //$table->string('issuer'); // TODO allow issuing credits by a staff member
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
        Schema::dropIfExists('credits');
    }
};
