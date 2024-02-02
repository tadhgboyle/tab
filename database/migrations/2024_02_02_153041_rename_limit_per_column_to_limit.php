<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_limits', function (Blueprint $table) {
            $table->renameColumn('limit_per', 'limit');
        });
    }
};
