<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockData extends Model
{
    use HasFactory;

    public function products(){
        return $this->belongsTo(Product::class, "product_id");
    }

    public function stock(){
       return $this->belongsTo(Stocks::class,"stock_id"); 
    }
}
