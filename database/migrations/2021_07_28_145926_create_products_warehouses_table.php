<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_warehouses', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('product_id');
            $table->bigInteger('ware_house_id');
            $table->integer('quantity');
            $table->double('value');
            $table->string('category');
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_warehouses');
    }
}
