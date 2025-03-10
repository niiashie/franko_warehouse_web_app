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
use App\Models\Admin;
use App\Models\Transaction;
use App\Models\WareHouseStaff;
use App\Models\WareHouse;
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
                              'date' => $transaction->transaction_date   //date("Y-m-d", strtotime($transaction->transaction_date))->timestamp  // Standardized date field
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
                              'type' => 'stock_change',
                              'quantity' => $stock_change->quantity,
                              'value' => $stock_change->products->price * $stock_change->quantity  , //$received->value,
                              'date' => $stock_change->created_at // Standardized date field
                           ];
                     });

      //Get Stock Data
      $stock_data = StockData::where("product_id",$product_id)->whereHas('stock', function ($query)  use ($ware_house_id) {
         $query->where('ware_house_id', $ware_house_id)->where('status',"approved");
      })->get()->map(function ($stock_data) {
                           return [
                              'user'=> "Accounts",
                              'type' => 'stock_data',
                              'quantity' => $stock_data->new_quantity,
                              'value' => $stock_data->new_value,
                              'date' => $stock_data->created_at // Standardized date field
                           ];
                     });

     
     
      $mergedResults = $transactions->merge($received_goods)->merge($stock_change)->merge($stock_data);

      // Sort by date in descending order (latest first)
      $sortedResults = $mergedResults->map(function ($item) {
         $item['date'] = Carbon::parse($item['date']); // Convert to Carbon
         return $item;
     })->sortByDesc('date')->values();
      //$mergedResults->sortBy('date')->values();
      
      $page = request()->get('page', 1);
      $perPage = 15; // Number of items per page

      // Manually paginate the collection
      $paginator = new LengthAwarePaginator(
         $sortedResults->forPage($page, $perPage), // Slice collection for current page
         $sortedResults->count(), // Total items
         $perPage, // Items per page
         $page, // Current page
         ['path' => request()->url(), 'query' => request()->query()] // Preserve query params
      );
      // Output the sorted collection
      return $paginator;

     

   }


   
   
   
}