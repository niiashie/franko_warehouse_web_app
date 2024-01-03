<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\WareHouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\WareHouseStaff;
use App\Models\ReceivedGoods;
use App\Models\Transaction;
use App\Models\Stocks;
use App\Models\StockData;
use Carbon\Carbon;

use function PHPUnit\Framework\isEmpty;

class PageController extends Controller
{
   public function index(){
       ///return view('index',compact('name','array'));
       return view('index');
   }

   public function proformer(){
    $wId = session('warehouse');
    $warehouse = WareHouse::find($wId);
  

    $data = [];
     if($wId != 0){
         $data = $warehouse->products;
     }
        return view('proformer',[
            "data" => $data,
            "warehouse" => $warehouse
        ]);
     
   }

   public function aboutUs(){
       return view('about');
   }

   public function stock(){
        $wId = session('warehouse');
        $result = [];
        $stockId = "";
        $status = "";
        $stockDate = "";
       

        $checkPresence = Stocks::where([['ware_house_id', '=', $wId]])
            ->where(function($q){
                    $q->where('status', '=', 'pending')
                    ->orWhere('status', '=','confirmed');
            })->get();

        if (!$checkPresence->isEmpty()) {
            /*$stockId = Stocks::where([
                ['ware_house_id',$wId],
                ['status','pending']
            ])->get()->pluck('id')->first();*/
            $stockId = Stocks::where([['ware_house_id', '=', $wId]])
            ->where(function($q){
                    $q->where('status', '=', 'pending')
                    ->orWhere('status', '=','confirmed');
            })->get()->pluck('id')->first();

            $status = Stocks::where([['ware_house_id', '=', $wId]])
            ->where(function($q){
                    $q->where('status', '=', 'pending')
                    ->orWhere('status', '=','confirmed');
            })->get()->pluck('status')->first();
            
            $stockDate =  Stocks::where([['ware_house_id', '=', $wId]])
            ->where(function($q){
                    $q->where('status', '=', 'pending')
                    ->orWhere('status', '=','confirmed');
            })->get()->pluck('created_at')->first();

            
            $result = StockData::where('stock_id',$stockId)->with('products')->get();            
        }

        $warehouse = WareHouse::where('id',$wId)->get()->first();


        return view('stock',[
          "stockData" => $result,
          "stockID" => $stockId,
          "warehouse" => $warehouse,
          "status"=> $status,
          "date" => $stockDate
        ]);
   }

   public function submitStock(Request $request){
     $stockId = $request->stockId;

     Stocks::where('id',$stockId)
                ->update([
                   'status'=>'confirmed'
                ]);
      return "Success";          
   }

   

   public function productDetail(Request $request){
       $productId = $request->pid;
       
       $productName = Product::where('id',$productId)->get()->pluck('name')->first();

       $transCollection = collect(); 

       $trans = Transaction::where('product_id',$productId)->with('products')->get();
       
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

       $receive = ReceivedGoods::where('product_id',$productId)->with('products')->get();
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

       $sortedResult = $sorted->values()->all();
     
       $resultCollection = collect();

       $resultCollection->push(
           [
               "product"=>$productName,
               "transaction"=>$sortedResult
           ]
       );

        return $resultCollection;
   }

   public function productCategoryInfo(String $productName){
    $result = collect();
    $product_categories = DB::table('products_warehouses')->where('category',$productName)
     ->join('products','products_warehouses.product_id','=','products.id')
     ->select('products_warehouses.*','products.name')->get();

      //Get all ware houses
      $all_warehouses = WareHouse::all();
      foreach($all_warehouses as $row){
        $warehouse_name = $row->wname;
        $warehouse_id = $row->id;
      
            $product_categories = DB::table('products_warehouses')->where([
                ['category', $productName],
                ['ware_house_id', $warehouse_id],
            ]
            )
            ->join('products','products_warehouses.product_id','=','products.id')
            ->select('products_warehouses.*','products.name')->get();

            $grouped = $product_categories->groupBy('category');
            
            //Get total quantity
            $quantity = DB::table('products_warehouses')
                    ->where([
                        ['category', $productName],
                        ['ware_house_id', $warehouse_id],
                    ]
                    )
                    ->sum('quantity');

            $totalValue = DB::table('products_warehouses')
                    ->where([
                        ['category', $productName],
                        ['ware_house_id', $warehouse_id],
                    ]
                    )
                    ->sum('value');


            $result->put($warehouse_name,
            [
                $grouped,
                "total_quantity"=> $quantity,
                "total_value"=> $totalValue
            ]
            );
            // $result->put($warehouse_name,$quantityCollect);
            

        }

    
       return view('productCategoryInfo',[
           "category"=> $productName,
           "data" => $result
       ]);
   }

   public function productDetail2( String $id){
     return view('productInfo',[
         "id" => $id
     ]);
   }

   public function transaction(){
       $wId = session('warehouse');
       $warehouse = WareHouse::find($wId);

       $today = Transaction::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->with('products')->get();
       
       $posts = Transaction::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->get()->groupBy(function($item) {
        return $item->invoice_no;
       });

       $data = [];
        if($wId != 0){
            $data = $warehouse->products;
        }
       return view('transaction',[
        "display"=>"overall",   
        "data" => $data,
        "today" => $today,
        "history" => $posts
       ]);
   }

   public function homeValues(){
    $wId = session('warehouse');    
    $res = Transaction::where('ware_house_id',$wId)
                        ->whereDate('created_at', '>', Carbon::now()->subDays(30))->with('products')
                        ->get()->groupBy('product_id');

     $resultArray = [];                    
     foreach($res as $key => $data){
            $productTotal = 0;
        foreach($data as $row){
            $productName = $row->products->name;
            $productTotal = $productTotal + $row->quantity;
        }
        $subResult = [
        "name" => $productName,
        "quantity" => $productTotal
        ];

        array_push($resultArray,$subResult);
     }  

    $price = array_column($resultArray, 'quantity');

    array_multisort($price, SORT_DESC, $resultArray);  




    return  $resultArray ;
    
   }

   public function leastQuantities(){
    $wId = session('warehouse');   
    $inventory = DB::table('products_warehouses')->where([
        ['ware_house_id','=',$wId],
        ['quantity','>','0']
    ])->join('products','products_warehouses.product_id','=','products.id')
    ->select('products_warehouses.*','products.name')
    ->orderBy('quantity','ASC')->get();

    return $inventory;
   }

   public function login(){
       return view('login');
   }

   public function inventory(){
       //aaaa
       $wId = session('warehouse');

       $warehouse = WareHouse::find($wId);

       $posts = ReceivedGoods::where('ware_house_id',$wId)->with(['products','warehouse'])->orderBy('created_at','DESC')->get()->groupBy(function($item) {
        return $item->created_at->format('Y-m-d');
       });
       $data = [];
       if($wId != 0){
           $data = $warehouse->products;
       }
       
       return view('inventory',[
        "data" => $data,
        "history"=>$posts
       ]);
   }

   public function checkProducts(){
        $wId = session('warehouse');
        $warehouse = WareHouse::find($wId);

        return $warehouse->products;
   }


   public function products(){
       $products = Product::where('pstatus', 'active')->latest()->get();

       $categories = ProductCategories::all();

       return view('products', [
           "products" => $products,
           "categories" => $categories
       ]);
   }

   public function warehouse(){
        $warehouse_managers = WarehouseStaff::where('role','Manager')->whereHas('warehouse',function($query){
            $query->where('wstatus', 'active');
        })->with(['admin','warehouse'])->latest()->get(); ;
        
        $active_members = WareHouseStaff::where([
            ['status', 'active'],
            ['role','!=',"Accountant"]
            ])->whereHas('warehouse', function($query){
            $query->where('wstatus', 'active');
            })->with(['admin','warehouse'])->latest()->get(); 

        $inactive_members = WareHouseStaff::where([
             ['status','inactive'],
             ['role','!=',"Accountant"]
            ])->with('admin')->get();

        $managed_warehouse = WareHouse::where('wstatus','active')->whereHas('staff',function($query){
        $query->where('role','Manager');
        })->get();


        //Getting unManagedWarehouse
        //Get all warehouses
        $all_warehouses = WareHouse::where('wstatus','active')->get();
        
        $un_managed_ware_house = [];
        /*$unmaneged = WareHouse::whereHas('staff', function($query){
            $query->where('role', '!=', 'Manager');
        })->get();*/

        foreach($all_warehouses as $row){
            $counter = 0;
            $id = $row->id;
            foreach($managed_warehouse as $row2){
                if($row2->id == $id){
                    $counter = $counter + 1;
                }
            }

            if($counter == 0){
                array_push($un_managed_ware_house,$row);
            }
        }

        $unassigned_staff = WareHouseStaff::where([
            ['ware_house_id','=',"0"],
            ['role','!=',"Accountant"],
            ['status',"=","active"]
        ])->with('admin')->get();

        $wId = session('warehouse');

        return view('warehouse',[
            'warehouse_managers' => $warehouse_managers,
            'active_members'=> $active_members,
            'inactive_members'=> $inactive_members,
            'managed_warehouse' => $managed_warehouse,
            'unmanaged_warehouse' => $un_managed_ware_house,
            'unassigned_staff' => $unassigned_staff,
            'warehouses' => $all_warehouses,
            'warehouseId' => $wId
            ]
        );
        
   }

   
   public function stockApprovalTest(){

     $result = collect();
     $product_categories = DB::table('products_warehouses')->where('category','Kepro')
      ->join('products','products_warehouses.product_id','=','products.id')
      ->select('products_warehouses.*','products.name')->get();

       //Get all ware houses
       $all_warehouses = WareHouse::all();
       foreach($all_warehouses as $row){
           $warehouse_name = $row->wname;
           $warehouse_id = $row->id;
         
           $product_categories = DB::table('products_warehouses')->where([
                ['category', 'Kepro'],
                ['ware_house_id', $warehouse_id],
             ]
            )
           ->join('products','products_warehouses.product_id','=','products.id')
           ->select('products_warehouses.*','products.name')->get();

           $grouped = $product_categories->groupBy('category');
            
           //Get total quantity
            $quantity = DB::table('products_warehouses')
                    ->where([
                        ['category', 'Kepro'],
                        ['ware_house_id', $warehouse_id],
                     ]
                    )
                    ->sum('quantity');

            $totalValue = DB::table('products_warehouses')
                    ->where([
                        ['category', 'Kepro'],
                        ['ware_house_id', $warehouse_id],
                     ]
                    )
                    ->sum('value');


            $result->put($warehouse_name,
              [
                  $grouped,
                  "total_quantity"=> $quantity,
                  "total_value"=> $totalValue
              ]
            );
           // $result->put($warehouse_name,$quantityCollect);
            

       }

     
        return $result;
   }

   public function home(){

       $wId = session('warehouse');
       $stockCheck = "granted";

       $totalTransaction = Transaction::where('ware_house_id',$wId)->sum('value');
       $totalReceivedGoods = ReceivedGoods::where('ware_house_id',$wId)->sum('value');
       $todaysTransaction = Transaction::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->sum('value');
       $todaysReceivedGoods = ReceivedGoods::where('ware_house_id',$wId)->whereDate('created_at', Carbon::today())->sum('value');
       $totalStockValue = DB::table('products_warehouses')->where('ware_house_id',$wId)->sum('value');

       //Check for pending stock;
       $checkPresence = Stocks::where([['ware_house_id', '=', $wId]])
       ->where(function($q){
               $q->where('status', '=', 'pending')
               ->orWhere('status', '=','confirmed');
       })->get();

       if (!$checkPresence->isEmpty()) { 
         $stockCheck = "denied";
       }

       $res = Transaction::where('ware_house_id','1')
                            ->whereDate('created_at', '>', Carbon::now()->subDays(30))->with('products')
                            ->get()->groupBy('product_id');

        $resultArray = [];                    
        foreach($res as $key => $data){
            //echo $key;
             $productTotal = 0;
            foreach($data as $row){
                $productName = $row->products->name;
                $productTotal = $productTotal + $row->quantity;
            }
            $subResult = [
                'name' => $productName,
                'quantity' => $productTotal
            ];

            array_push($resultArray,$subResult);
        }  
        $price = array_column($resultArray, 'quantity');
        array_multisort($price, SORT_DESC, $resultArray); 
        
        
        //Product Categories Breakdown

        $result_collection = collect(); 

        //Get product categories
        $product_category_quantity = collect();
        $product_categories = ProductCategories::all();
        foreach($product_categories as $categories){
            $category_name = $categories->name;
            $quantity = DB::table('products_warehouses')
                    ->where('category', '=', $category_name)
                    ->sum('quantity');
            $product_category_quantity->put($category_name,$quantity); 
        }
        
        $result_collection->put("product_categories",$product_category_quantity);
            

       return view('home',[
           "totalTransaction" => $totalTransaction,
           "totalReceivedGoods" => $totalReceivedGoods,
           "todaysTransaction" => $todaysTransaction,
           "todaysReceivedGoods" => $todaysReceivedGoods,
           "totalStockValue" => $totalStockValue,
           "mostTransaction" =>  $resultArray,
           "stockAccess"=> $stockCheck,
           "productCategories"=> $result_collection
       ]);
   }

   public function accountant(Request $request){
      $wname = $request->wname;
      $wid = $request->wid;
      $accountId = $request->accountId;
      $accountName = $request->accountName;
      $accountUserId = $request->accountUserId;
 
      session(
                        
        [
         'id' => $accountId,
         'name' => $accountName,
         'user_id'=> $accountUserId,
         'role' => "Accountant",
         'warehouse'=> $wid,
         'warehouse_name'=> $wname
        ]
     );

      return "Success";
   }

   public function loginData(Request $request){
        $request ->validate([
           'login_user_id' => 'required|string',
           'login_password' => 'required|min:5|max:12|string'
       ]);
    
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
                return view('login',[
                    "accountant" => $accountant,
                    "warehouses"=> $warehouses
                ]);
             }
             else{
                if($obj[0]->role == "member"){
                    return back()->withErrors(['Please contact admin to verify your account', '']);
                 }
                 else if($obj[0]->ware_house_id == "0"){
                    return back()->withErrors(['Staff has not been assigned to any warehouse yet', '']);
                 }
                 else{
                    $login2 = DB::table('admins')
                                ->join('ware_house_staff','admins.id','=','ware_house_staff.uid')
                                ->join('ware_houses','ware_house_staff.ware_house_id','=','ware_houses.id')
                                ->select('admins.id','admins.name','admins.user_id','admins.password','ware_house_staff.role','ware_house_staff.ware_house_id','ware_houses.wname')
                                ->where('user_id',$loginId)
                                ->get(); 
                    $obj2 = json_decode($login2);
    
                    session(

                        [
                         'id' => $obj2[0]->id,
                         'name' => $obj2[0]->name,
                         'user_id'=> $obj2[0]->user_id,
                         'role' => $obj2[0]->role,
                         'warehouse'=> $obj2[0]->ware_house_id,
                         'warehouse_name'=> $obj2[0]->wname
                        ]
                    );
                        //home($obj[0]->name);
                        return redirect('/home');
                    
                 }
             }
             
           
             
           }else{
            return back()->withErrors(['Invalid password', '']);
           }
        }else{
            return back()->withErrors(['Invalid user id', '']);
        }
   }

   public function saveData(Request $request){
        $request -> validate([
          'name' => 'required',
          'user_id'=>'required|min:4|max:6|string|unique:admins,user_id',
          'password'=>'required|min:6|max:12',
          'confirm_password'=>'required|same:password',
          'adminId' => 'required|min:4|max:6|string'
       ]);
       
       $enteredAdminId = $request->adminId;
       $hashPassword = Hash::make($request->password);

       $admin = DB::table('admins')
                    ->join('ware_house_staff','admins.id','=','ware_house_staff.uid')
                    ->select('admins.user_id','ware_house_staff.role')
                    ->where('user_id',$enteredAdminId)
                    ->get();
       
       
       if (!$admin->isEmpty()) { 
           $obj = json_decode($admin);
           $role =  $obj[0]->role;

          if($role !='Manager'){
            return back()->withErrors(['Invalid manager ID', '']);
          }else{

            $insertId = Admin::insertGetId(
                [
                'name'=> $request->name,
                'user_id' => $request->user_id,
                'password' => $hashPassword
                ]
            );

            

            $ware_house_staff = new WareHouseStaff;
            $ware_house_staff->uid = $insertId;
            $ware_house_staff->role = 'member';
            $ware_house_staff->status = 'inactive';
            $ware_house_staff->ware_house_id = '0';
            $saveWarehouseStaff = $ware_house_staff->save();



            if($saveWarehouseStaff){
                return back()->with('success','Registration successful, contact manager for account activation');
            }else{
                return back()->withErrors(['Please check Internet connection', '']);
            }

            
          


          }
         

          

       }
       else{
         return back()->withErrors(['Invalid admin ID', '']);
       }

       
       
   }

   public function test(){
       /*
        $result_collection = collect(); 

        //Get product categories
        $product_category_quantity = collect();
        $product_categories = ProductCategories::all();
        foreach($product_categories as $categories){
            $category_name = $categories->name;
            $quantity = DB::table('products_warehouses')
                    ->where('category', '=', $category_name)
                    ->sum('quantity');
            $product_category_quantity->put($category_name,$quantity); 
        }
        
        $result_collection->put("product_categories",$product_category_quantity);

        return $result_collection;*/
        $wId = session('warehouse');

        $warehouse = WareHouse::find($wId);
        $data = $warehouse->products;
        //$data = DB::table('products_warehouses')->where('')
        return $data;
   }

   public function getProductList(){
       $res = Product::all();
       return $res;
   }

   public function getProductStock(){
      return Product::with('warehouses')->get();
   }

   public function test2(){
    $wId = session('warehouse');

    $warehouse = WareHouse::find($wId);
    return $wId;
   }

   public function updateStock(Request $request){
     $wId = session('warehouse');
     $id = $request->id;
     $price = $request->price;
     $new_quantity = $request->new_quantity;
    
     $value = $price * $new_quantity;

     DB::table('products_warehouses')->where('product_id',$id)->where('ware_house_id',$wId)->update([
        'quantity'=> $new_quantity,
        'value' => $value
     ]);

     return "Success";
   }
   
  


}