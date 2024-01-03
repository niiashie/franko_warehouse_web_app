<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class WareHouse extends Model
{
    use HasFactory;

    public function staff(){
        return $this->hasMany(WareHouseStaff::class, "ware_house_id");
    }

    public function products(){
        return $this->belongsToMany(Product::class,'products_warehouses','ware_house_id','product_id')->withPivot('id','quantity','value','category');
    }
}
