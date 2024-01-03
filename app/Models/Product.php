<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

     public function category() {
        return $this->belongsTo(ProductCategories::class, "category_id");
     }

     public function warehouses(){
        return $this->belongsToMany(WareHouse::class,'products_warehouses','product_id','ware_house_id')->withPivot('id','quantity','value','category');
     }

     
}
