<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\ReceivedGoods;
use App\Models\WareHouse;
use App\Models\WareHouseStaff;
use App\Models\User;
use App\Models\Stocks;
use App\Models\StockData;
use Carbon\Carbon;
use App\Models\Requisition;
use Exception;

class UserController extends Controller{

    public function registration(Request $request){
       
         $validator = Validator::make($request->all(), [
            'name' => 'required',
            'pin'=>'required|min:4|max:6|string|unique:users,pin',
            'password'=>'required|min:6|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),202);
        } else {
           //return "Good to go";
           //$allData = $request->all();
           //$allData['password'] = bcrypt($allData['password']);
           
           $currentUser = new User;
           $currentUser->name = $request->name;
           $currentUser->pin = $request->pin;
           $currentUser->password = bcrypt($request->password);
           $currentUser->status = 'pending';

           $currentUserSave = $currentUser->save();

           //$user = User::create($allData);
           $resArray = [];
           if($currentUserSave){
               
                $resArray['message'] = 'Registration Successful';
                //$resArray['token'] = $currentUser->createToken('api-application')->accessToken;
                //$resArray['warehouse'] = $managedWarehouse;
                return response()->json($resArray,200);
           }else{
                $resArray['message'] = 'Ooops!! An error occurred during transaction';
                return response()->json($resArray,200);
           }
          

          
        }

    }

    public function login(Request $request){
        $resArr = [];
        if(Auth::attempt(
            [
                'pin' => $request->pin, 
                'password' => $request->password
              ]
            )
        ){
            $managedWarehouse = WareHouse::where('wstatus','active')->whereHas('staff',function($query){
                $query->where('role','Manager');
                })->get();
            $user = User::where('pin',$request->pin)->get()->first();
            
            $status = $user->status;
            if($status!='pending'){
                $resArr['message'] = 'Login Successful';
                $resArr['warehouse'] = $managedWarehouse;
                $resArr['token'] = $user->createToken('api-application')->accessToken;
                $resArr['user'] = $user;
            }
            else{
                $resArr['message'] = 'Please contact admin to activate account';
            }
           
            return response()->json($resArr,200);

        }else{
            $resArr['message'] = 'Invalid Credentials';
            return response()->json($resArr,202);
        }
    }

    public function managedWareHouses(){
        $managedWarehouse = WareHouse::where('wstatus','active')->whereHas('staff',function($query){
            $query->where('role','Manager');
            })->get();

        return $managedWarehouse;   
    }

    public function warehouseDetails(Request $request){
        $wId = $request->wid;
        $resArr = [];
        if($wId != null){
            $transaction30 = Transaction::where('ware_house_id',$wId)->whereDate('created_at', '>', Carbon::now()->subDays(30))->sum('value');

            $todaysTransaction = Transaction::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->sum('value');
            $todaysReceivedGoods = ReceivedGoods::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->sum('value');
            $totalStockValue = DB::table('products_warehouses')->where('ware_house_id',$wId)->sum('value');
    
            $overallStockValue = DB::table('products_warehouses')->sum('value');
    
            $resArr['message'] = "Successful";
            $resArr['transaction30'] = $transaction30;
            $resArr['todayTransaction'] = $todaysTransaction;
            $resArr['todayReceivedGoods'] = $todaysReceivedGoods;
            $resArr['stockValue'] = $totalStockValue;
            $resArr['overallStock'] = $overallStockValue;
    
            return response()->json($resArr,200);
        }
        else{
            $resArr['message'] = "Invalid request";
            return response()->json($resArr,202);
        }
       

    }

    public function transactionDetails(Request $request){
        $wId = $request->wid;
        $resArr = [];
        if($wId != null){
            $today = Transaction::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->with('products')->get();
       
            $posts = Transaction::where('ware_house_id',$wId)->with(['products','warehouse'])->orderBy('created_at','DESC')->get()->groupBy(function($item) {
             return $item->created_at->format('Y-m-d');
            });

            $resArr['message'] = "Successful";
            $resArr['today'] = $today;
            $resArr['history'] = $posts;
            return response()->json($resArr,200);
        }
        else{
            $resArr['message'] = "Invalid request";
            return response()->json($resArr,202);
        }

    }

    public function receivedGoodsDetails(Request $request){
        $wId = $request->wid;
        $resArr = [];

        if($wId != null){
            $today = ReceivedGoods::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->with('products')->get();

            $posts = ReceivedGoods::where('ware_house_id',$wId)->with(['products','warehouse'])->orderBy('created_at','DESC')->get()->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            });

            $resArr['message'] = "Successful";
            $resArr['today'] = $today;
            $resArr['history'] = $posts;
            return response()->json($resArr,200);
        }
        else{
            $resArr['message'] = "Invalid request";
            return response()->json($resArr,202);
        }
        

    }

    public function getInventory(Request $request){
        $wId = $request->wid;
        $resArr = [];

        if($wId != null){
            $warehouse = WareHouse::find($wId);
            $resArr['inventory'] = $warehouse->products;
            return response()->json($resArr,200);
        }
        else{
            $resArr['message'] = "Invalid request";
            return response()->json($resArr,202); 
        }

    }

    public function getAccountants(){
        $result = WareHouseStaff::where('role','Accountant')->with('admin')->get();
        return $result;
    }

    public function getRequisitionNotification(){
        $res =  Requisition::select('*')->with('warehouse')->orderBy('created_at', 'desc')->get();
        return $res;
    }

    public function confirmRequisition(Request $request){
        $id = $request->id;
        $resArr = [];
        if($id == null){
            $resArr['message'] = "Invalid request";
            return response()->json($resArr,202); 
        }
        else{

            try{
                Requisition::where('id', $id)->update(['status' => "complete"]);
                $resArr['message'] = "Success";
                return response()->json($resArr,200); 
            }catch(Exception $e) {
                $resArr['message'] = "Failed";
                return response()->json($resArr,200); 
            }
        
        }     
    }

    public function processStock(Request $request){
      $resArray = [];  
      $stock_taker_id = $request->stock_taker_id;
      $ware_house_id = $request->ware_house_id;
      $stock_obj = json_decode($request->stock_values);
      $counter = 0;

      //Check for pending stock
       $pendingStocks = Stocks::where([['ware_house_id', '=', $ware_house_id]])->where(function($q){
                $q->where('status', '=', 'pending')
                ->orWhere('status', '=','confirmed');
        })->get();
       if ($pendingStocks->isEmpty()) {
            $currentStock = new Stocks;
            $currentStock->stock_taker_id = $stock_taker_id;
            $currentStock->ware_house_id = $ware_house_id;
            $currentStock->status = 'pending';

            $currentUserSave = $currentStock->save();

            if($currentUserSave){
                $insertId = $currentStock->id;
                

                foreach($stock_obj as $obj){
                    $product_id = $obj->product_id;
                    $price = $obj->price;
                    $old_quantity = $obj->old_value;
                    $new_quantity = $obj->new_value;
                    $difference = $old_quantity - $new_quantity;

                    $old_value = $old_quantity * $price;
                    $new_value = $new_quantity * $price;
                    $difference_value = $difference * $price;

                    $stockData = new StockData;
                    $stockData->stock_id = $insertId;
                    $stockData->product_id = $product_id;
                    $stockData->old_quantity = $old_quantity;
                    $stockData->new_quantity = $new_quantity;
                    $stockData->difference_quantity = $difference;
                    $stockData->old_value = $old_value;
                    $stockData->new_value = $new_value;
                    $stockData->difference_value = $difference_value;

                    $stockData->save();

                }

                $resArr['message'] = "Stock submitted successfully";
                

            }
       }else{
         $resArr['message'] = 'Current warehouse has pending stock';
       }
      /*foreach($stock_obj as $obj){
        $counter = $counter + 1;
      }*/
      return response()->json($resArr);
    }

    public function getConfirmedStocks(){

        $confirmedStock = collect(); 

        $result = Stocks::where('status','confirmed')->with(['admin','warehouse'])->get();
        foreach($result as $row){
            $id = $row->id;

            //Get sum differences

            $quantity_difference = StockData::where("stock_id",$id)->sum('difference_quantity');
            $old_value_sum = StockData::where("stock_id",$id)->sum('old_value');
            $new_value_sum = StockData::where("stock_id",$id)->sum('new_value');
            $value_difference = StockData::where("stock_id",$id)->sum('difference_value');

            $confirmedStock->push(
                [
                    "stock"=>$row,
                    "quantity_difference"=>$quantity_difference,
                    "value_difference"=>$value_difference,
                    "old_value"=>$old_value_sum,
                    "new_value"=>$new_value_sum
                ]
            );

        }
        return $confirmedStock;
    }

    public function approveStock(Request $request){
       $stockId = $request->stockId;
       $warehouseId = $request->warehouseId;

       try{
            Stocks::where('id', $stockId)->update(['status' => "approved"]);
            
            
            //Update inventory values with stock values;
            $res2 = StockData::where("stock_id",$stockId)->get();
            foreach($res2 as $row){
                $productId = $row->product_id;
                $newQuantity = $row->new_quantity;
                $newValue = $row->new_value;

                DB::table('products_warehouses')->where(
                    [
                        ['product_id', $productId],
                        ['ware_house_id',$warehouseId]
                    ]
                    )->update(
                    
                        [
                            'quantity'      => $newQuantity,
                            'value'             => $newValue,
                        ]
                    );
            }
            $resArr['message'] = "Success";
            return response()->json($resArr,200); 
        }catch(Exception $e) {
            $resArr['message'] = "Stock Approval Failed";
            return response()->json($resArr,200); 
        }
    }

}
