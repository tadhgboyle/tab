<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    public function up()
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
