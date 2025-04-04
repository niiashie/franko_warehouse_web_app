<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    public function warehouse(){
        return $this->belongsTo(WareHouse::class,'warehouse_id');
    }

    public function approver(){
        return $this->belongsTo(Admin::class,'approver_id');
    }
}
