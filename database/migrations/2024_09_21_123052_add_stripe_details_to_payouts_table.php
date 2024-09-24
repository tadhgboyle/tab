<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->renameColumn('cashier_id', 'creator_id');

            $table->string('status')->after('amount');
            $table->string('stripe_payment_intent_id')->after('status')->nullable();
            $table->string('stripe_checkout_session_id')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            //
        });
    }
};
