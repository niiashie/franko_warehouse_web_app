<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_data', function (Blueprint $table) {
            $table->id();
            $table->string('stock_id');
            $table->string('product_id');
            $table->decimal('old_quantity');
            $table->decimal('new_quantity');
            $table->decimal('difference_quantity');
            $table->double('old_value',8,2);
            $table->double('new_value',8,2);
            $table->double('difference_value',8,2);
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
        Schema::dropIfExists('stock_data');
    }
}
