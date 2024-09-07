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
        Schema::create('gift_card_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_card_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('assigner_id');
            $table->foreign('assigner_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('users');

            $table->unique(['gift_card_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_card_assignments');
    }
};
