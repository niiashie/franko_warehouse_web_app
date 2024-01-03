<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReceivedGoods;
use App\Models\Requisition;


class InventoryController extends Controller
{
    public function receiveGoods(Request $request){
        $obj = json_decode($request->res);
        $products = $obj->products;
        $requisitionId = $obj->requisition_id;
        
        $warehouse = $obj->warehouse_id;

        foreach($products as $product){
           //DB::table('products_warehouses')
           $res = DB::table('products_warehouses')->where([
               ['ware_house_id',$warehouse],
               ['product_id',$product->product_id]
           ])->get();
           
           $previous_value = $res[0]->value;
           $previous_quantity = $res[0]->quantity;

           $total_quantity = $previous_quantity + $product->product_quantity;
           $total_value = $previous_value + $product->product_value;

           $update = DB::table('products_warehouses')->where([
                ['ware_house_id',$warehouse],
                ['product_id',$product->product_id]
            ])->update([
                'quantity'=> $total_quantity,
                'value' => $total_value
            ]);


            $goods = new ReceivedGoods;
            $goods->product_id = $product->product_id;
            $goods->ware_house_id = $warehouse;
            $goods->quantity = $product->product_quantity;
            $goods->value = $product->product_value;
            $goods->requisition_id = $requisitionId;
            $goods->save();
           
        }
        echo "Success";
        //$products->product_name;
    }

    public function requestRequisition(Request $request){
        $reason = $request->reason;
        $wid = $request->wid;
 
        $requisition = new Requisition;
        $requisition->warehouse_id = $wid;
        $requisition->reason = $reason;
        $requisition->status = "pending";
        $result = $requisition->save();

        $insertId = $requisition->id;
        if($result){
            echo "Success ".$insertId;
        }else{
            echo "Failure";
        }

    }

    public function inventoryHistory(){
        $posts = ReceivedGoods::where('ware_house_id','1')->with(['products','warehouse'])->orderBy('created_at')->get()->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
       });

       return $posts;
    }
}
