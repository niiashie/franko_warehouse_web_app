<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WareHouse;
use App\Models\Admin;

class WareHouseStaff extends Model
{
    use HasFactory;

    public function warehouse(){
        return $this->belongsTo(WareHouse::class, "ware_house_id");
    }

    public function admin(){
        return $this->belongsTo(Admin::class, "uid");
    }


}
