<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    public function warehouse(){
        return $this->belongsTo(WareHouseStaff::class, "uid");
    }
   
}
