<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReceivedGoods;
use App\Models\ProductCategories;
use App\Models\Requisition;
use App\Models\Product;
use App\Models\StockChange;
use App\Models\StockData;
use App\Models\Stocks;
use App\Models\Admin;
use App\Models\Transaction;
use App\Models\WareHouseStaff;
use App\Models\WareHouse;
use App\Models\Customer;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class WebController extends Controller{

   public function login(Request $request){
        $request ->validate([
           'login_user_id' => 'required|string',
           'login_password' => 'required|min:5|max:12|string'
       ]);
       $resArr = [];
    
       $loginId = $request->login_user_id;
       $login = DB::table('admins')
                    ->join('ware_house_staff','admins.id','=','ware_house_staff.uid')
                    ->select('admins.id','admins.name','admins.user_id','admins.password','ware_house_staff.role','ware_house_staff.ware_house_id')
                    ->where('user_id',$loginId)
                    ->get();

        if (!$login->isEmpty()){
          // return $login;
           $obj = json_decode($login);
           $password =  $obj[0]->password;
           if(Hash::check($request->login_password,$password)){

             if($obj[0]->role == "Accountant"){
                $accountant = Admin::where('user_id',$loginId)->first();
                $warehouses = WareHouse::all();
              
                $resArr['message'] = 'Accountant implementation pending';
                return response()->json($resArr,202);
             }
             else{
                if($obj[0]->role == "member"){
                    $resArr['message'] = 'Please contact admin to verify your account';
                    return response()->json($resArr,202);
                   
                 }
                 else if($obj[0]->ware_house_id == "0"){
                    $resArr['message'] = 'Staff has not been assigned to any warehouse yet';
                    return response()->json($resArr,202);
                   
                 }
                 else{
                   
                    $admin = Admin::where('user_id',$loginId)->first();
                    $warehouse = WareHouseStaff::where('uid',$admin->id)->with('warehouse')->get();
                    $resArr['message'] = 'Login Successful';
                    $resArr['token'] = $admin->createToken('API Token')->plainTextToken;
                    $resArr['user'] = $admin;
                    $resArr['warehouse'] = $warehouse;

                    return response()->json($resArr,200);
                 
                       
                    
                 }
             }
             
           
             
           }else{
            $resArr['message'] = 'Invalid Password';
            return response()->json($resArr,202);
           }
        }else{
            $resArr['message'] = 'Invalid User ID';
            return response()->json($resArr,202);
        }
   }

   public function paginate($items, $perPage = 15, $page = null, $options = [])
   {
         $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
         //$items = $items instanceof Collection ? $items : Collection::make($items);
         return new LengthAwarePaginator(collect($items)->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, $options);
   }

   public function getCategoryProducts($category_id,$warehouse_id){
     $category = ProductCategories::where('id',$category_id)->first();
     $category_name = $category->name;

     $category_products = DB::table('products_warehouses')->where('category', '=', $category_name )->where('ware_house_id',$warehouse_id)->get();
     $result = [];
     foreach($category_products as $stock){
      
       $object = [];
       $object['id'] = $stock->id;
       $object['quantity'] = $stock->quantity;
       $object['value'] = $stock->value;
       $object['product'] = Product::where('id',$stock->product_id)->with('category')->first(); 
       array_push($result,$object);
     }

     return $result;
    
   }

   public function getDashboardValues(String $id){
     $resArr = [];
     //Get product categories
     $product_category_quantity = [];  //collect();
     $product_categories = ProductCategories::all();
     foreach($product_categories as $categories){
         $category = [];
         $category_name = $categories->name;
         $category['id'] = $categories->id;
         $category['name'] = $categories->name;
         $quantity = DB::table('products_warehouses')
                 ->where('category', '=', $category_name )
                 ->where('ware_house_id', '=', $id )
                 ->sum('quantity');
         $category['quantity'] = $quantity; 
         array_push($product_category_quantity,$category);       
       
     }
    
     $res = Transaction::where('ware_house_id',$id)
                         -> select(DB::raw('DATE(transaction_date) as date'),DB::raw('SUM(value) as total_sales'))
                         ->groupBy('date')
                         ->orderBy('date', 'DESC')->take(15)->get();
                        //  ->whereDate('created_at', '>', Carbon::now()->subDays(30))
                        // ->groupBy('date')->get();
 
     
     $todaysTransaction = Transaction::where('ware_house_id',$id)->whereDate('created_at', Carbon::today())->sum('value');
     $todaysReceivedGoods = ReceivedGoods::where('ware_house_id',$id)->whereDate('created_at', Carbon::today())->sum('value');
     $totalStockValue = DB::table('products_warehouses')->where('ware_house_id',$id)->sum('value');
     $totalStockQuantity = DB::table('products_warehouses')->where('ware_house_id',$id)->sum('quantity');
     //$result_collection->put("product_categories",$product_category_quantity);
     $resArr['categories'] = $product_category_quantity;
     $resArr['todays_transaction'] = $todaysTransaction;
     $resArr['todays_received_goods'] = $todaysReceivedGoods;
     $resArr['stock_quantity'] = $totalStockQuantity;
     $resArr['stock_value'] = $totalStockValue;
     $resArr['transactions'] = $res;

     return $resArr;
   }

   public function getWarehouse(){
      return WareHouse::with(['staff'=>function($query){
         $query->with('admin');
       }])->get();
   }

   public function addWarehouse(Request $request){
      $request -> validate([
         'ware_house_name' => 'required|unique:ware_houses,wname',
         'ware_house_location'=>'required',
         'ware_house_branch'=>'required',
      ]);

      $ware_house = new WareHouse;
      $ware_house->wname = $request->ware_house_name;
      $ware_house->wlocation = $request->ware_house_location;
      $ware_house->wbranch  = $request->ware_house_branch;
      $ware_house->wstatus = 'active';

      $result = $ware_house->save();
      
      $insertId = $ware_house->id;
      $result2  = $insertId."_".$request->ware_house_name;
      $resArr = [];

      if($result){

         $products =  Product::where('pstatus', 'active')->with('category')->latest()->get();
         
         foreach($products as $product){
             $ware_house->products()->attach([
                 $product->id =>[
                     'quantity' => 0,
                     'value' => 0.00,
                     'category'=>$product->category->name
                 ]
             ]);
         }
         return response()->json($resArr,200);
        
      }
   }

   public function changeStaffStatus(Request $request){
      $request -> validate([
         'ware_house_id' =>  'required',
         'staff_status' => 'required',
         'ware_house_staff_id'=>'required',
      ]);

      $resArr = [];

      $user_id = $request->ware_house_staff_id;
      $status = $request->staff_status;
      $ware_house_id = $request-> ware_house_id;

      DB::table('ware_house_staff')
      ->where('uid', $user_id)->where('ware_house_id', $ware_house_id)
      ->update([
          'status'=> $status
          ]);
      
      $resArr['message'] = "User status successfully changed";              
      return response()->json($resArr,200);        
   }

   public function changeRoles(Request $request){
      $request -> validate([
         'ware_house_id' =>  'required',
         'ware_house_role'=> 'required',
         'ware_house_staff_id'=>'required',
      ]);

      $resArr = [];

      $user_id = $request->ware_house_staff_id;
      $role = $request->ware_house_role;
      $ware_house_id = $request-> ware_house_id;

      DB::table('ware_house_staff')
                ->where('uid', $user_id)->where('ware_house_id', $ware_house_id)
                ->update([
                    'role'=> $role
                    ]);
      $resArr['message'] = "User role successfully changed";              
      return response()->json($resArr,200);           
   }

   public function assignStaff(Request $request){
      $request -> validate([
         'staff_id' =>  'required',
         'role'=> 'required',
         'ware_house_id'=>'required',
        ]);
     $staff_id = $request->staff_id;
     $role = $request->role;
     $ware_house_id = $request->ware_house_id;

     DB::table('ware_house_staff')->insert([
      'uid' => $staff_id,
      'ware_house_id' => $ware_house_id,
      'role' => $role,
      'status' => "active",
      'created_at' => now(),
      'updated_at' => now(),
     ]);
     
     $resArr['message'] = "Successfully assigned user to warehouse";              
     return response()->json($resArr,200); 

   }

   public function getUnassignedStaff(String $id){
     $users = Admin::all();
     $ware_house_staff = DB::table('ware_house_staff')->where('ware_house_id',$id)->get();
     $unassigned_staff = [];

     foreach($users as $admins){
       $counter = 0;
       foreach($ware_house_staff as $staff){
         if($admins->id == $staff->id){
            $counter++;
         }
       }
       if($counter == 0){
         array_push($unassigned_staff,$admins);
       }
     }
    
     return $unassigned_staff;

   }

   public function getProduct(Request $request){
     $query = Product::query();
     
     if($request->has('keyword')){
       $keyword = $request->query('keyword');
       $result = Product::where('name', 'like', "%$keyword%")
                    ->orWhere('origin', 'like', "%$keyword%")
                    ->orWhereHas('category', function ($query) use ($keyword) {
                     $query->where('name', 'like', "%$keyword%");
                 })->with('category')->get();
       return $this->paginate($result);          
     }
     else{
      return $this->paginate(Product::with('category')->get());
   
     }
     
   }
   
   public function getProductCategories(){
      return ProductCategories::all();
   }

   public function createProduct(Request $request){
      $request -> validate([
         'name' =>  'required',
         'origin'=> 'required',
         'category_id'=>'required',
         'price'=>'required',
        ]);

      $product = new Product;
      $product->name = $request->name;
      $product->origin = $request->origin;
      $product->category_id = $request->category;
      $product->price =  $request->price;
      $product->pstatus = 'active';

      $productSave = $product->save();
      $productId = $product->id;
      if($productSave){
         
         $warehouses = WareHouse::all();
         $currentProduct = Product::where('id', $productId)->with('category')->latest()->get();

         foreach($warehouses as $warehouse){
               $product->warehouses()->attach([
                  $warehouse->id => [
                     'quantity' => 0,
                     'value' => 0.00,
                     'category'=>$currentProduct[0]->category->name
                  ]
               ]);
         }
         $resArr = [];
         $resArr['message'] = "Successfully assigned user to warehouse";              
         return response()->json($resArr,200); 
      }else{
         $resArr = [];
         $resArr['message'] = "An error has occurred";              
         return response()->json($resArr,400); 
        
      }
 

      // DB::table('ware_house_staff')->insert([
      //    'uid' => $staff_id,
      //    'ware_house_id' => $ware_house_id,
      //    'role' => $role,
      //    'status' => "active",
      //    'created_at' => now(),
      //    'updated_at' => now(),
      //   ]);
   }

   public function updateProduct(Request $request, $id){
     
      $product = Product::find($id);

      if (!$product) {
          return response()->json(['message' => 'Product not found!'], 404);
      }
      
       // Validate request data
      $validatedData = $request->validate([
         'name' => 'required|string|max:255',
         'price' => 'required|numeric|min:0',
         'origin' => 'required|string|max:255',
         'category_id' => 'required|integer|max:255'
      ]);

      $product->update($validatedData);

      return response()->json([
         'message' => 'Product updated successfully!',
         'product' => $product
      ], 200);
   }

   public function getProductMovement(Request $request){
      $request -> validate([
      'product_id' =>  'required',
      'ware_house_id'=>'required',
      ]);
      //Check Transactions
     $ware_house_id = $request->ware_house_id;
     $product_id = $request->product_id;

     $results = [];

     //Get Product Transactions
     $transactions =  Transaction::where("product_id",$product_id)->where("ware_house_id",$ware_house_id)->with('admin')->get()
                        ->map(function ($transaction) {
                           return [
                              'user'=> $transaction->admin,
                              'type' => 'transaction',
                              'quantity' => $transaction->quantity,
                              'value' => $transaction->value,
                              'date' => $transaction->created_at   //date("Y-m-d", strtotime($transaction->transaction_date))->timestamp  // Standardized date field
                           ];
                     });


     //Get Received Goods
     $received_goods = ReceivedGoods::where("product_id",$product_id)->where("ware_house_id",$ware_house_id)->with('admin')->get()
                        ->map(function ($received) {
                           return [
                              'user'=> $received->admin,
                              'type' => 'received',
                              'quantity' => $received->quantity,
                              'value' => $received->value,
                              'date' => $received->created_at // Standardized date field
                           ];
                     });

      //Get Stock Changes
      $stock_change = StockChange::where("product_id",$product_id)->where("warehouse_id",$ware_house_id)->with(['admin','products'])->get()
                        ->map(function ($stock_change) {
                           return [
                              'user'=> $stock_change->admin,
                              'type' => 'stock_adjustment',
                              'quantity' => $stock_change->new_quantity,
                              'value' => $stock_change->products->price * $stock_change->new_quantity  , //$received->value,
                              'date' => $stock_change->created_at // Standardized date field
                           ];
                     });

      //Get Stock Data
      $stock_data = StockData::where("product_id",$product_id)->whereHas('stock', function ($query)  use ($ware_house_id) {
         $query->where('ware_house_id', $ware_house_id)->where('status',"approved");
      })->get()->map(function ($stock_data) {
                           return [
                              'user'=> "Accounts",
                              'type' => 'opening_balance',
                              'quantity' => $stock_data->new_quantity,
                              'value' => $stock_data->new_value,
                              'date' => $stock_data->created_at // Standardized date field
                           ];
                     });

      
      if($request->has('transaction_only')){
         $mergedResults = $transactions;
      }
      else if($request->has('received_only')){
         $mergedResults = $received_goods;
      }
      else{
         $mergedResults = $transactions -> concat($received_goods)->concat ($stock_change)->concat($stock_data); 
      }               
      
    

      // Sort by date in descending order (latest first)
      $results = $mergedResults->map(function ($item) {
         $item['date'] = Carbon::parse($item['date']); // Convert to Carbon
         return $item;
     })->sortByDesc('date')->values();
      //$mergedResults->sortBy('date')->values();

      $sortedResults = collect($results);
      
      $page = request()->get('page', 1);
      $perPage = 15; // Number of items per page

      // Manually paginate the collection
      $paginator = new LengthAwarePaginator(
         $sortedResults->forPage($page, $perPage)->values(), // Slice collection for current page
         $sortedResults->count(), // Total items
         $perPage, // Items per page
         $page, // Current page
         ['path' => request()->url(), 'query' => request()->query()] // Preserve query params
      );
      // Output the sorted collection
      return response()->json($paginator);  

     

   }

   public function getStockTakings(Request $request){
     if($request->has('ware_house_id')){
       $ware_house_id = $request->query('ware_house_id');
       return $this->paginate(Stocks::where('ware_house_id',$ware_house_id)->with(['admin','warehouse'])->latest()->get());
     } 
     return  $this->paginate(Stocks::with(['admin','warehouse'])->latest()->get());
   }

   public function getProductStockBalance(Request $request){
     $product_id = $request->query("product_id");
     $ware_house_id = $request->query('ware_house_id');

     $start_date = $request->query('start_date');
     $end_date = $request->query('end_date');

     //echo $start_date;
     
     $stock_data = StockData::where("product_id",$product_id)->whereHas('stock', function ($query)  use ($ware_house_id, $start_date) {
                  $query->where('ware_house_id', $ware_house_id)->where('status',"approved")->whereDate('created_at','=', Carbon::parse($start_date));
               })->get()->map(function ($stock_data) use ($start_date) {
                        return [
                           'user'=> "Accounts",
                           'type' => 'opening_balance',
                           'quantity' => $stock_data->new_quantity,
                           'value' => $stock_data->new_value,
                           'date' => $start_date //$stock_data->created_at // Standardized date field
                        ];
                  });
      
      $transactions =  Transaction::where("product_id",$product_id)->where("ware_house_id",$ware_house_id)->whereDate('created_at','>=', Carbon::parse($start_date))->whereDate('created_at','<=', Carbon::parse($end_date))->with('admin')->get()
         ->map(function ($transaction) {
            return [
               'user'=> $transaction->admin,
               'type' => 'transaction',
               'quantity' => $transaction->quantity,
               'value' => $transaction->value,
               'date' => $transaction->created_at  
            ];
      }); 

      $received_goods = ReceivedGoods::where("product_id",$product_id)->where("ware_house_id",$ware_house_id)->whereDate('created_at','>=', Carbon::parse($start_date))->whereDate('created_at','<=', Carbon::parse($end_date))->with('admin')->get()
         ->map(function ($received) {
            return [
               'user'=> $received->admin,
               'type' => 'received',
               'quantity' => $received->quantity,
               'value' => $received->value,
               'date' => $received->created_at // Standardized date field
            ];
      });
      
      $mergedResults = $stock_data -> concat($transactions)->concat($received_goods); 

      $results = $mergedResults->map(function ($item) {
         $item['date'] = Carbon::parse($item['date']); // Convert to Carbon
         return $item;
      })->sortBy('date')->values();

      $sortedResults = collect($results);
      
      $page = request()->get('page', 1);
      $perPage = 15; // Number of items per page

      // Manually paginate the collection
      $paginator = new LengthAwarePaginator(
         $sortedResults->forPage($page, $perPage)->values(), // Slice collection for current page
         $sortedResults->count(), // Total items
         $perPage, // Items per page
         $page, // Current page
         ['path' => request()->url(), 'query' => request()->query()] // Preserve query params
      );
      // Output the sorted collection
      return response()->json($paginator);  ;

     // return $results;
   }


   public function addProductCategories(Request $request){
      $request ->validate([
          'name' => 'required|string|unique:product_categories,name',
      ]);

      $product_categories = new ProductCategories;
      $product_categories->name = $request->name;
      $product_category_save = $product_categories->save();

      if($product_category_save){
         $resArr['message'] = "Successfully added product category";              
         return response()->json($resArr,200);
      }
      else{
         $resArr['message'] = "An error has occurred";              
         return response()->json($resArr,202);
         
      }

     
   }

   public function getWarehouseInventory(Request $request,String $id){
     $ware_house_id = $id;
    

     if($request->has('keyword')){
         $keyword = $request->query('keyword');
         $res = Product::where('name', 'like', "%$keyword%")
                     ->orWhere('origin', 'like', "%$keyword%")
                     ->orWhereHas('category', function ($query) use ($keyword) {
                     $query->where('name', 'like', "%$keyword%");
                  })->with(['warehouses'=> function ($query) use($ware_house_id) {
                     $query->where('ware_house_id', $ware_house_id);
                    }, "category"])->get();
         return $this->paginate($res);           
                   
     }else{
      $res = Product::with(['warehouses'=> function ($query) use($ware_house_id) {
         $query->where('ware_house_id', $ware_house_id);
        }, "category"])->get();
        return $this->paginate($res);
     }

     

   }

   public function getAllProducts(String $id){
      $ware_house_id = $id;
      $res = Product::with(['warehouses'=> function ($query) use($ware_house_id) {
        $query->where('ware_house_id', $ware_house_id);
      }, "category"])->get();
      return response()->json($res);
   }

   public function sendRequisition(Request $request){
      $data = $request->data;
      $reason = $request->reason;
      $warehouse = $request->warehouse;
      $user_id = $request->user_id;
      
      $resArr = [];
      $requisition = new Requisition;
      $requisition->warehouse_id = $warehouse;
      $requisition->reason = $reason;
      $requisition->status = "pending";
      $result = $requisition->save();

      $insertId = $requisition->id;
      if($result){
         foreach($data as $item){
           $product_id = $item['product_id'];
           $quantity = $item['quantity'];
           $value = $item['value'];

           ReceivedGoods::insert([
            'product_id' => $product_id,
            'user_id' => $user_id,
            'ware_house_id' => $warehouse,
            'requisition_id' => $insertId,
            'quantity' => $quantity,
            'value' => $value, 
            'created_at' => now(),
            'updated_at' => now(),
           ]);
         }
         $resArr['message'] = 'Successfully sent requisition, please contact Director for acceptance';
         return response()->json($resArr,200);
      }else{
         $resArr['message'] = 'Requisition not sent, please check network';
         return response()->json($resArr,202);
        
      }
   }

   public function getRequisition(String $id){
      return $this->paginate(Requisition::where('warehouse_id',$id)->with('approver')->latest()->get());
   }

   public function getRequisitionProducts(String $id){
     return  ReceivedGoods::where('requisition_id',$id)->with(['products','admin'])->get();
   }

   public function rejectRequisition(String $id){
     Requisition::where("id",$id)->delete();
     ReceivedGoods::where("requisition_id",$id)->delete();
     $resArr = [];
     $resArr['message'] = 'Requisition deleted';
     return response()->json($resArr,200);
   }

   public function acceptRequisition(Request $request){
      $request ->validate([
         'requisition_id' => 'required|string',
         'approver_id' => 'required'
      ]); 
      $id = $request->requisition_id;
      $approver_id = $request->approver_id;

      Requisition::where("id",$id)->update([
         "status" => "complete",
         "approver_id" => $approver_id
      ]);
      
      $goods = ReceivedGoods::where("requisition_id",$id)->with('products')->get();
      foreach($goods as $item){
        $price = $item->products->price;
        $quantity = $item->quantity;
        $value = $price * $quantity;
        $warehouse_id = $item->ware_house_id;
        $product_id = $item->product_id;

        $inventory = DB::table('products_warehouses')
        ->where('product_id', '=', $product_id )
        ->where('ware_house_id', '=', $warehouse_id ) ->first();

        $previous_quantity = $inventory->quantity;
        $new_quantity = $previous_quantity + $quantity;

        $new_value = $new_quantity * $price;

        DB::table('products_warehouses')
        ->where('product_id', '=', $product_id )
        ->where('ware_house_id', '=', $warehouse_id )->update([
         "quantity" =>  $new_quantity,
         "value" => $new_value
        ]);
        

      }

      $resArr['message'] = 'Requisition accepted';
      return response()->json($resArr,200);
   }

   public function getTransactions(Request $request){
      if($request->has('type')){
         $type = $request->input('type');
         $date = $request->input('date');
         if($type == "Transaction Date"){
            $transaction = Transaction::where('transaction_date',$date)->select(
               'invoice_no',
               'transaction_type',
               'customer_name',
               'transaction_date',
               'status',
               DB::raw('SUM(value) as total_value'),
               DB::raw('SUM(quantity) as total_quantity'),
               DB::raw('MAX(created_at) as latest_created_at')
            )->groupBy('invoice_no', 'transaction_type', 'customer_name', 'transaction_date','status')->orderByDesc('latest_created_at')->get();
         }
         else if($type == "Creation Date"){
              $transaction = Transaction::whereDate('created_at',$date)->select(
               'invoice_no',
               'transaction_type',
               'customer_name',
               'transaction_date',
               'status',
               DB::raw('SUM(value) as total_value'),
               DB::raw('SUM(quantity) as total_quantity'),
               DB::raw('MAX(created_at) as latest_created_at')
            )->groupBy('invoice_no', 'transaction_type', 'customer_name', 'transaction_date','status')->orderByDesc('latest_created_at')->get();
         }
      }
      else{
         if($request->has('keyword')){
            $keyword = $request->input('keyword');
            $transaction = Transaction::select(
               'invoice_no',
               'transaction_type',
               'customer_name',
               'transaction_date',
               'status',
               DB::raw('SUM(value) as total_value'),
               DB::raw('SUM(quantity) as total_quantity'),
               DB::raw('MAX(created_at) as latest_created_at')
            )->when($keyword, function ($query) use ($keyword) {
               $query->where(function ($q) use ($keyword) {
                   $q->where('invoice_no', 'like', "%$keyword%")
                     ->orWhere('transaction_type', 'like', "%$keyword%")
                     ->orWhere('customer_name', 'like', "%$keyword%");
               });
           })->groupBy('invoice_no', 'transaction_type', 'customer_name', 'transaction_date','status')->orderByDesc('latest_created_at')->get();
         }else{
            $transaction = Transaction::select(
               'invoice_no',
               'transaction_type',
               'customer_name',
               'status',
               'transaction_date',
               DB::raw('SUM(value) as total_value'),
               DB::raw('SUM(quantity) as total_quantity'),
               DB::raw('MAX(created_at) as latest_created_at')
            )->groupBy('invoice_no', 'transaction_type', 'customer_name', 'transaction_date','status')->orderByDesc('latest_created_at')->get();
      
         }
      }
      
     
      return $this->paginate($transaction);

   }

   public function getCustomers(Request $request){
      if($request->has('keyword')){
         $keyword = $request->query('keyword');
         $result = Customer::where('name', 'like', "%$keyword%")
                      ->orWhere('location', 'like', "%$keyword%")
                      ->orWhere('phone','like',"%$keyword%")
                     ->get();
         return $this->paginate($result);          
      }else{
         return $this->paginate(Customer::all());
      }
     
   }

   public function addCustomer(Request $request){
      $request ->validate([
         'name' => 'required|string|unique:customers,name',
         'phone' => 'required|unique:customers,phone',
         'location' => 'required'
      ]); 
      
      Customer::insert([
         'name' => $request->name,
         'location' => $request->location,
         'phone' => $request->phone,
         'balance' => 0,
         'created_at' => now(),
         'updated_at' => now(),
        ]);

      $resArr['message'] = 'Successfully created customer';
      return response()->json($resArr,200);   

   }

   public function getAllCustomers(){
      return Customer::all();
   }

   public function makeTransaction(Request $request){

      $request ->validate([
         'data' => 'required',
         'type' => 'required',
         'warehouse_id' => 'required',
         'user_id' => 'required',
         'transaction_date' => 'required',
         'transaction_type' => 'required'
      ]); 
      $data = $request->data;
      $type = $request->type;
      $warehouse = $request->warehouse_id;
      $user_id = $request->user_id;
      $customer_id = $request->customer_id;
      $customer_name = $request->customer_name;
      $transaction_date = $request->transaction_date;
      $transaction_type = $request->transaction_type;
      $warehouse_location = $request->warehouse_location;

      if($transaction_type == "Credit Sale"){
         $status = "unpaid";
      }
      else{
         $status = "paid";
      }

      $error_message = "";

       //Get last known transaction
      $last_transaction = Transaction::latest()->first();
      $id = (int)$last_transaction->invoice_no;
      $invoice_number = "000".($id + 1);
      
      foreach($data as $item){
         //DB::table('products_warehouses')
         $res = DB::table('products_warehouses')->where([
             ['ware_house_id',$warehouse],
             ['product_id',$item['product_id']]
         ])->get();
         
         $previous_value = $res[0]->value;
         $previous_quantity = $res[0]->quantity;

         $new_quantity = $previous_quantity - $item['quantity'];
         $new_value = $previous_value - $item['value'];
         if($new_quantity >= 0){
            

             $exists =  DB::table('transactions')
                 ->where('product_id', $product->product_id)
                 ->where('user_id', $user_id)
                 ->where('ware_house_id', $warehouse)
                 ->where('quantity', $product->product_quantity)
                 ->where('value', $product->product_value)
                 ->where('transaction_type', $transactionType)
                 ->where('transaction_date', $transactionDate)
                 ->where('customer_name', $customerName)
                 ->where('created_at', '>=', now()->subSeconds(10)) // Check last 30 seconds
                 ->exists();

             if ($exists) {
                 break;
                 $error_message = "Duplicate Detected";
                 $error_counter = $error_counter + 1; 
                 //return response()->json(['message' => 'Duplicate transaction detected!'], 409);
             }
             else{
                 $update = DB::table('products_warehouses')->where([
                 ['ware_house_id',$warehouse],
                 ['product_id',$item['product_id']]
                 ])->update([
                     'quantity'=>  $new_quantity,
                     'value' => $new_value
                 ]);
                 
                 $goods = new Transaction;
                 $goods->product_id = $item['product_id'];
                 $goods->ware_house_id = $warehouse;
                 $goods->quantity = $item['quantity'];
                 $goods->value = $item['value'];
                 $goods->invoice_no = $invoice_number;
                 $goods->transaction_date = $transaction_date;
                 $goods->transaction_type = $transaction_type;
                 $goods->ware_house_name = $warehouse_location;
                 $goods->customer_name = $customer_name;
                 $goods->customer_id = $customer_id;
                 $goods->user_id = $user_id;
                 $goods->status = $status;
                 $goods->save();     
             }


             
         }
         else{
            $error_counter = $error_counter + 1; 
         }

       
         
     }

      if($error_counter > 0){
       $resArr['message'] = $error_message;
       return response()->json($resArr,401); 
      }
      else{
         $resArr['message'] = 'Successfully created transaction';
         $resArr['transaction'] = Transaction::where('invoice_no',$invoice_number)->get();
        return response()->json($resArr,200);
      }
      // foreach($data as $item){
      //    $product_id = $item['product_id'];
      //    $quantity = $item['quantity'];
      //    $value = $item['value'];

      //    ReceivedGoods::insert([
      //     'product_id' => $product_id,
      //     'user_id' => $user_id,
      //     'ware_house_id' => $warehouse,
      //     'requisition_id' => $insertId,
      //     'quantity' => $quantity,
      //     'value' => $value, 
      //     'created_at' => now(),
      //     'updated_at' => now(),
      //    ]);
      // }
   }

   

  

  


   
   
   
}