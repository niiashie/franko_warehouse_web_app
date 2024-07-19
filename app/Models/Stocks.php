<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stocks extends Model
{
    use HasFactory;

    public function admin(){
        return $this->belongsTo(User::class, "stock_taker_id");
    }

    public function warehouse(){
        return $this->belongsTo(WareHouse::class, "ware_house_id");
    }
}
