<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Requisition;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\ReceivedGoods;
use App\Models\WareHouse;

class TransactionController extends Controller
{
    public function transact(Request $request){
       
        $obj = json_decode($request->res);
        $invoiceNumber = $obj->transaction_invoice;

        //Validate invoice numer
        $invoice_validator = Transaction::where('invoice_no',$invoiceNumber)->get();
        if (!$invoice_validator->isEmpty()) { 
           echo "Invoice Number Already Chosen";
        } 
        else{
            $products = $obj->products;
            $transactionType = $obj->transaction_type;
            $transactionDate = $obj->transaction_date;
            $customerName = $obj->transaction_customer;
            $transactionWarehouse = $obj->transaction_warehouse;
    
            $warehouse = $obj->warehouse_id;
    
            foreach($products as $product){
                //DB::table('products_warehouses')
                $res = DB::table('products_warehouses')->where([
                    ['ware_house_id',$warehouse],
                    ['product_id',$product->product_id]
                ])->get();
                
                $previous_value = $res[0]->value;
                $previous_quantity = $res[0]->quantity;
     
                $new_quantity = $previous_quantity - $product->product_quantity;
                $new_value = $previous_value - $product->product_value;
     
                $update = DB::table('products_warehouses')->where([
                     ['ware_house_id',$warehouse],
                     ['product_id',$product->product_id]
                 ])->update([
                     'quantity'=>  $new_quantity,
                     'value' => $new_value
                 ]);
     
     
                 $goods = new Transaction;
                 $goods->product_id = $product->product_id;
                 $goods->ware_house_id = $warehouse;
                 $goods->quantity = $product->product_quantity;
                 $goods->value = $product->product_value;
                 $goods->invoice_no = $invoiceNumber;
                 $goods->transaction_date = $transactionDate;
                 $goods->transaction_type = $transactionType;
                 $goods->ware_house_name = $transactionWarehouse;
                 $goods->customer_name = $customerName;
                 $goods->save();
                
            }
            echo "Success";
        }

       
        
    }

    public function todayTrans(){
       
       $transCollection = collect(); 
       $productName = Product::where('id','2')->get()->pluck('name')->first();
       $trans = Transaction::where('product_id',2)->with('products')->get();
       
       foreach($trans as $transaction){
           $transCollection->push(
             [
                 "name"=> $transaction->products->name,
                 "cost"=> $transaction->products->price,
                 "value"=> $transaction->value,
                 "date"=> $transaction->created_at,
                 "quantity"=> $transaction->quantity,
                 "category" => 'transaction'
             ]
           );
       }

       $receive = ReceivedGoods::where('product_id',2)->with('products')->get();
       foreach($receive as $goods){
        $transCollection->push(
            [
                "name"=> $goods->products->name,
                "cost"=> $goods->products->price,
                "value"=> $goods->value,
                "date"=> $goods->created_at,
                "quantity"=> $goods->quantity,
                "category" => 'received'
            ]
          );
       }

       $sorted = $transCollection->sortByDesc('date');

       $sorted->values()->all();
       $resultCollection = collect();

       $resultCollection->push(
           [
               "product"=>$productName,
               "transaction"=>$sorted
           ]
       );

       $res =  Requisition::select('*')->with('warehouse')->get();
       return  $sorted;//$resultCollection;
    }

    public function getTransaction(Request $request){
        $request ->validate([
            'transactionHistoryDate' => 'required|string',
        ]);
    
        $wId = session('warehouse');
        $warehouse = WareHouse::find($wId);

        $transactionDate = $request->transactionHistoryDate;
        
        
        $posts = Transaction::where('ware_house_id',$wId)->whereDate('transaction_date',$transactionDate )->get()->groupBy(function($item) {
            return $item->invoice_no;
        });
       
        $today = Transaction::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->with('products')->get();
       

        $data = [];
        if($wId != 0){
            $data = $warehouse->products;
        }
        
        return view('transaction',[
            "display"=>"history",   
            "data" => $data,
            "today" => $today,
            "history" => $posts
        ]);
    }
}
