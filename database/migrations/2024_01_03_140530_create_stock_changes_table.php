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
        Schema::create('stock_changes', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->integer('user_id');
            $table->string('warehouse_id');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_changes');
    }
};
