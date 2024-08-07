<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    public function up()
    {
        Schema::table('user_limits', function (Blueprint $table) {
            $table->dropColumn('limit_id');
        });

        Schema::table('user_limits', function (Blueprint $table) {
            $table->primary(['user_id', 'category_id']);
        });
    }
};
