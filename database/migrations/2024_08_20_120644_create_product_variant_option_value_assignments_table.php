<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variant_option_value_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained(indexName: 'pv');
            $table->foreignId('product_variant_option_id')->constrained(indexName: 'pvo');
            $table->foreignId('product_variant_option_value_id')->constrained(indexName: 'pvov');
            $table->timestamps();

            $table->unique(['product_variant_id', 'product_variant_option_id'], name: 'unique_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_option_assignments');
    }
};
