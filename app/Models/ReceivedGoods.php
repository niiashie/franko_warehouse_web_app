<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivedGoods extends Model
{
    use HasFactory;

    public function products(){
        return $this->belongsTo(Product::class,'product_id');
    }

    public function warehouse(){
        return $this->belongsTo(WareHouse::class,'ware_house_id');
    }

    public function admin(){
        return $this->belongsTo(Admin::class, "user_id");
    }
}
