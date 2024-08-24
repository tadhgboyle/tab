<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('gift_card_user');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
