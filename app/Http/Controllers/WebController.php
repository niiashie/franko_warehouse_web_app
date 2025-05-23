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
use App\Models\Payment;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class WebController extends Controller
{

   public function login(Request $request)
   {
      $request->validate([
         'login_user_id' => 'required|string',
         'login_password' => 'required|min:5|max:12|string'
      ]);
      $resArr = [];

      $loginId = $request->login_user_id;
      $login = DB::table('admins')
         ->join('ware_house_staff', 'admins.id', '=', 'ware_house_staff.uid')
         ->select('admins.id', 'admins.name', 'admins.user_id', 'admins.password', 'ware_house_staff.role', 'ware_house_staff.ware_house_id')
         ->where('user_id', $loginId)
         ->get();

      if (!$login->isEmpty()) {
         // return $login;
         $obj = json_decode($login);
         $password =  $obj[0]->password;
         if (Hash::check($request->login_password, $password)) {

            if ($obj[0]->role == "Accountant") {
               $accountant = Admin::where('user_id', $loginId)->first();
               $warehouses = WareHouse::all();

               $resArr['message'] = 'Accountant implementation pending';
               return response()->json($resArr, 202);
            } else {
               if ($obj[0]->role == "member") {
                  $resArr['message'] = 'Please contact admin to verify your account';
                  return response()->json($resArr, 202);
               } else if ($obj[0]->ware_house_id == "0") {
                  $resArr['message'] = 'Staff has not been assigned to any warehouse yet';
                  return response()->json($resArr, 202);
               } else {

                  $admin = Admin::where('user_id', $loginId)->first();
                  $warehouse = WareHouseStaff::where('uid', $admin->id)->with('warehouse')->get();
                  $resArr['message'] = 'Login Successful';
                  $resArr['token'] = $admin->createToken('API Token')->plainTextToken;
                  $resArr['user'] = $admin;
                  $resArr['warehouse'] = $warehouse;

                  return response()->json($resArr, 200);
               }
            }
         } else {
            $resArr['message'] = 'Invalid Password';
            return response()->json($resArr, 202);
         }
      } else {
         $resArr['message'] = 'Invalid User ID';
         return response()->json($resArr, 202);
      }
   }

   public function register(Request $request)
   {
      $request->validate([
         'name' => 'required',
         'user_id' => 'required|min:4|max:6|string|unique:admins,user_id',
         'password' => 'required|min:6|max:12',
         'confirm_password' => 'required|same:password',
         'adminId' => 'required|min:4|max:6|string'
      ]);

      $enteredAdminId = $request->adminId;
      $hashPassword = Hash::make($request->password);

      $admin = DB::table('admins')
         ->join('ware_house_staff', 'admins.id', '=', 'ware_house_staff.uid')
         ->select('admins.user_id', 'ware_house_staff.role')
         ->where('user_id', $enteredAdminId)
         ->get();


      if (!$admin->isEmpty()) {
         $obj = json_decode($admin);
         $role =  $obj[0]->role;

         if ($role != 'Manager') {
            $resArr['message'] = 'Invalid Manager ID ' . $role;
            return response()->json($resArr, 400);
            //return back()->withErrors(['Invalid manager ID', '']);
         } else {

            $insertId = Admin::insertGetId(
               [
                  'name' => $request->name,
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



            if ($saveWarehouseStaff) {
               $resArr['message'] = 'Registration successful, contact manager for account activation';
               return response()->json($resArr, 200);
            } else {
               $resArr['message'] = 'An error has occured';
               return response()->json($resArr, 400);
            }
         }
      } else {
         return back()->withErrors(['Invalid admin ID', '']);
      }
   }

   public function paginate($items, $perPage = 15, $page = null, $options = [])
   {
      $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
      //$items = $items instanceof Collection ? $items : Collection::make($items);
      return new LengthAwarePaginator(collect($items)->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, $options);
   }

   public function getCategoryProducts($category_id, $warehouse_id)
   {
      $category = ProductCategories::where('id', $category_id)->first();
      $category_name = $category->name;

      $category_products = DB::table('products_warehouses')->where('category', '=', $category_name)->where('ware_house_id', $warehouse_id)->get();
      $result = [];
      foreach ($category_products as $stock) {

         $object = [];
         $object['id'] = $stock->id;
         $object['quantity'] = $stock->quantity;
         $object['value'] = $stock->value;
         $object['product'] = Product::where('id', $stock->product_id)->with('category')->first();
         array_push($result, $object);
      }

      return $result;
   }

   public function getDashboardValues(String $id)
   {
      $resArr = [];
      //Get product categories
      $product_category_quantity = [];  //collect();
      $product_categories = ProductCategories::all();
      foreach ($product_categories as $categories) {
         $category = [];
         $category_name = $categories->name;
         $category['id'] = $categories->id;
         $category['name'] = $categories->name;
         $quantity = DB::table('products_warehouses')
            ->where('category', '=', $category_name)
            ->where('ware_house_id', '=', $id)
            ->sum('quantity');
         $category['quantity'] = $quantity;
         array_push($product_category_quantity, $category);
      }

      $res = Transaction::where('ware_house_id', $id)
         ->select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(value) as total_sales'))
         ->groupBy('date')
         ->orderBy('date', 'DESC')->take(15)->get();
      //  ->whereDate('created_at', '>', Carbon::now()->subDays(30))
      // ->groupBy('date')->get();


      $todaysTransaction = Transaction::where('ware_house_id', $id)->whereDate('created_at', Carbon::today())->sum('value');
      $todaysReceivedGoods = ReceivedGoods::where('ware_house_id', $id)->whereDate('created_at', Carbon::today())->sum('value');
      $totalStockValue = DB::table('products_warehouses')->where('ware_house_id', $id)->sum('value');
      $totalStockQuantity = DB::table('products_warehouses')->where('ware_house_id', $id)->sum('quantity');
      //$result_collection->put("product_categories",$product_category_quantity);
      $resArr['categories'] = $product_category_quantity;
      $resArr['todays_transaction'] = $todaysTransaction;
      $resArr['todays_received_goods'] = $todaysReceivedGoods;
      $resArr['stock_quantity'] = $totalStockQuantity;
      $resArr['stock_value'] = $totalStockValue;
      $resArr['transactions'] = $res;

      return $resArr;
   }

   public function getWarehouse()
   {
      return WareHouse::with(['staff' => function ($query) {
         $query->with('admin');
      }])->get();
   }

   public function addWarehouse(Request $request)
   {
      $request->validate([
         'ware_house_name' => 'required|unique:ware_houses,wname',
         'ware_house_location' => 'required',
         'ware_house_branch' => 'required',
      ]);

      $ware_house = new WareHouse;
      $ware_house->wname = $request->ware_house_name;
      $ware_house->wlocation = $request->ware_house_location;
      $ware_house->wbranch  = $request->ware_house_branch;
      $ware_house->wstatus = 'active';

      $result = $ware_house->save();

      $insertId = $ware_house->id;
      $result2  = $insertId . "_" . $request->ware_house_name;
      $resArr = [];

      if ($result) {

         $products =  Product::where('pstatus', 'active')->with('category')->latest()->get();

         foreach ($products as $product) {
            $ware_house->products()->attach([
               $product->id => [
                  'quantity' => 0,
                  'value' => 0.00,
                  'category' => $product->category->name
               ]
            ]);
         }
         return response()->json($resArr, 200);
      }
   }

   public function changeStaffStatus(Request $request)
   {
      $request->validate([
         'ware_house_id' =>  'required',
         'staff_status' => 'required',
         'ware_house_staff_id' => 'required',
      ]);

      $resArr = [];

      $user_id = $request->ware_house_staff_id;
      $status = $request->staff_status;
      $ware_house_id = $request->ware_house_id;

      DB::table('ware_house_staff')
         ->where('uid', $user_id)->where('ware_house_id', $ware_house_id)
         ->update([
            'status' => $status
         ]);

      $resArr['message'] = "User status successfully changed";
      return response()->json($resArr, 200);
   }

   public function changeRoles(Request $request)
   {
      $request->validate([
         'ware_house_id' =>  'required',
         'ware_house_role' => 'required',
         'ware_house_staff_id' => 'required',
      ]);

      $resArr = [];

      $user_id = $request->ware_house_staff_id;
      $role = $request->ware_house_role;
      $ware_house_id = $request->ware_house_id;

      DB::table('ware_house_staff')
         ->where('uid', $user_id)->where('ware_house_id', $ware_house_id)
         ->update([
            'role' => $role
         ]);
      $resArr['message'] = "User role successfully changed";
      return response()->json($resArr, 200);
   }

   public function assignStaff(Request $request)
   {
      $request->validate([
         'staff_id' =>  'required',
         'role' => 'required',
         'ware_house_id' => 'required',
      ]);
      $staff_id = $request->staff_id;
      $role = $request->role;
      $ware_house_id = $request->ware_house_id;

      //Check for staff presence
      $staff = DB::table('ware_house_staff')->where('uid', $staff_id)->where('ware_house_id', 0)->get();
      if (!$staff->isEmpty()) {
         DB::table('ware_house_staff')->where('uid', $staff_id)->where('ware_house_id', 0)->update([
            'ware_house_id' => $ware_house_id,
            'role' => $role
         ]);
      } else {
         DB::table('ware_house_staff')->insert([
            'uid' => $staff_id,
            'ware_house_id' => $ware_house_id,
            'role' => $role,
            'status' => "active",
            'created_at' => now(),
            'updated_at' => now(),
         ]);
      }


      $resArr['message'] = "Successfully assigned user to warehouse";
      return response()->json($resArr, 200);
   }

   public function getUnassignedStaff(String $id)
   {
      $users = Admin::all();
      $ware_house_staff = DB::table('ware_house_staff')->where('ware_house_id', $id)->get();
      $unassigned_staff = [];

      foreach ($users as $admins) {
         $counter = 0;
         foreach ($ware_house_staff as $staff) {
            if ($admins->id == $staff->id) {
               $counter++;
            }
         }
         if ($counter == 0) {
            array_push($unassigned_staff, $admins);
         }
      }

      return $unassigned_staff;
   }

   public function getProduct(Request $request)
   {
      $query = Product::query();

      if ($request->has('keyword')) {
         $keyword = $request->query('keyword');
         $result = Product::where('name', 'like', "%$keyword%")
            ->orWhere('origin', 'like', "%$keyword%")
            ->orWhereHas('category', function ($query) use ($keyword) {
               $query->where('name', 'like', "%$keyword%");
            })->with('category')->get();
         return $this->paginate($result);
      } else {
         return $this->paginate(Product::with('category')->get());
      }
   }

   public function getProductCategories()
   {
      return ProductCategories::all();
   }

   public function createProduct(Request $request)
   {
      $request->validate([
         'name' =>  'required',
         'origin' => 'required',
         'category_id' => 'required',
         'price' => 'required',
      ]);

      $product = new Product;
      $product->name = $request->name;
      $product->origin = $request->origin;
      $product->category_id = $request->category;
      $product->price =  $request->price;
      $product->pstatus = 'active';

      $productSave = $product->save();
      $productId = $product->id;
      if ($productSave) {

         $warehouses = WareHouse::all();
         $currentProduct = Product::where('id', $productId)->with('category')->latest()->get();

         foreach ($warehouses as $warehouse) {
            $product->warehouses()->attach([
               $warehouse->id => [
                  'quantity' => 0,
                  'value' => 0.00,
                  'category' => $currentProduct[0]->category->name
               ]
            ]);
         }
         $resArr = [];
         $resArr['message'] = "Successfully assigned user to warehouse";
         return response()->json($resArr, 200);
      } else {
         $resArr = [];
         $resArr['message'] = "An error has occurred";
         return response()->json($resArr, 400);
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

   public function updateProduct(Request $request, $id)
   {

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

      //update product warehouses table
      $result = DB::table('products_warehouses')->where('product_id', '=', $id)->get();
      foreach ($result as $res) {
         $quantity = $res->quantity;
         $value = $quantity * $request->price;
         DB::table('products_warehouses')->where('id', $res->id)->update([
            "value" => $value,
         ]);
      }

      return response()->json([
         'message' => 'Product updated successfully!',
         'product' => $product
      ], 200);
   }

   public function getProductMovement(Request $request)
   {
      $request->validate([
         'product_id' =>  'required',
         'ware_house_id' => 'required',
      ]);
      //Check Transactions
      $ware_house_id = $request->ware_house_id;
      $product_id = $request->product_id;

      $results = [];

      //Get Product Transactions
      $transactions =  Transaction::where("product_id", $product_id)->where("ware_house_id", $ware_house_id)->with('admin')->get()
         ->map(function ($transaction) {
            return [
               'user' => $transaction->admin,
               'type' => 'transaction',
               'quantity' => $transaction->quantity,
               'invoice_no' => $transaction->invoice_no,
               'value' => $transaction->value,
               'date' => $transaction->created_at   //date("Y-m-d", strtotime($transaction->transaction_date))->timestamp  // Standardized date field
            ];
         });


      //Get Received Goods
      $received_goods = ReceivedGoods::where("product_id", $product_id)->where("ware_house_id", $ware_house_id)->with('admin')->get()
         ->map(function ($received) {
            return [
               'user' => $received->admin,
               'type' => 'received',
               'quantity' => $received->quantity,
               'invoice_no' => "N/A",
               'value' => $received->value,
               'date' => $received->created_at // Standardized date field
            ];
         });

      //Get Stock Changes
      $stock_change = StockChange::where("product_id", $product_id)->where("warehouse_id", $ware_house_id)->with(['admin', 'products'])->get()
         ->map(function ($stock_change) {
            return [
               'user' => $stock_change->admin,
               'type' => 'stock_adjustment',
               'quantity' => $stock_change->new_quantity,
               'invoice_no' => "N/A",
               'value' => $stock_change->products->price * $stock_change->new_quantity, //$received->value,
               'date' => $stock_change->created_at // Standardized date field
            ];
         });

      //Get Stock Data
      $stock_data = StockData::where("product_id", $product_id)->whereHas('stock', function ($query)  use ($ware_house_id) {
         $query->where('ware_house_id', $ware_house_id)->where('status', "approved");
      })->get()->map(function ($stock_data) {
         return [
            'user' => "Accounts",
            'type' => 'opening_balance',
            'quantity' => $stock_data->new_quantity,
            'invoice_no' => "N/A",
            'value' => $stock_data->new_value,
            'date' => $stock_data->created_at // Standardized date field
         ];
      });


      if ($request->has('transaction_only')) {
         $mergedResults = $transactions;
      } else if ($request->has('received_only')) {
         $mergedResults = $received_goods;
      } else {
         $mergedResults = $transactions->concat($received_goods)->concat($stock_change)->concat($stock_data);
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

   public function getStockTakings(Request $request)
   {
      if ($request->has('ware_house_id')) {
         $ware_house_id = $request->query('ware_house_id');
         return $this->paginate(Stocks::where('ware_house_id', $ware_house_id)->with(['admin', 'warehouse'])->latest()->get());
      }
      return  $this->paginate(Stocks::with(['admin', 'warehouse'])->latest()->get());
   }

   public function getProductStockBalance(Request $request)
   {
      $product_id = $request->query("product_id");
      $ware_house_id = $request->query('ware_house_id');

      $start_date = $request->query('start_date');
      $end_date = $request->query('end_date');

      //echo $start_date;

      $stock_data = StockData::where("product_id", $product_id)->whereHas('stock', function ($query)  use ($ware_house_id, $start_date) {
         $query->where('ware_house_id', $ware_house_id)->where('status', "approved")->whereDate('created_at', '=', Carbon::parse($start_date));
      })->get()->map(function ($stock_data) use ($start_date) {
         return [
            'user' => "Accounts",
            'type' => 'opening_balance',
            'invoice_no' => "N\A",
            'customer_name' => "N\A",
            'quantity' => $stock_data->new_quantity,
            'value' => $stock_data->new_value,
            'date' => $start_date //$stock_data->created_at // Standardized date field
         ];
      });

      $transactions =  Transaction::where("product_id", $product_id)->where('status', '!=', 'reversed')->where("ware_house_id", $ware_house_id)->whereDate('created_at', '>=', Carbon::parse($start_date))->whereDate('created_at', '<=', Carbon::parse($end_date))->with('admin')->get()
         ->map(function ($transaction) {
            return [
               'user' => $transaction->admin,
               'type' => 'transaction',
               'quantity' => $transaction->quantity,
               'invoice_no' => $transaction->invoice_no,
               'customer_name' => str_contains($transaction->transaction_type, "Requisition") ? $transaction->transaction_type : $transaction->customer_name,
               'value' => $transaction->value,
               'date' => $transaction->created_at
            ];
         });

      $reversed_transactions =  Transaction::where("product_id", $product_id)->where('status', 'reversed')->where("ware_house_id", $ware_house_id)->whereDate('created_at', '>=', Carbon::parse($start_date))->whereDate('created_at', '<=', Carbon::parse($end_date))->with('admin')->get()
         ->map(function ($transaction) {
            return [
               'user' => $transaction->admin,
               'type' => 'reversed',
               'quantity' => $transaction->quantity,
               'invoice_no' => $transaction->invoice_no,
               'customer_name' => "N\A",
               'value' => $transaction->value,
               'date' => $transaction->created_at
            ];
         });

      $received_goods = ReceivedGoods::where("product_id", $product_id)->where("ware_house_id", $ware_house_id)->whereDate('created_at', '>=', Carbon::parse($start_date))->whereDate('created_at', '<=', Carbon::parse($end_date))->with('admin')->get()
         ->map(function ($received) {
            return [
               'user' => $received->admin,
               'invoice_no' => "N\A",
               'type' => 'received',
               'customer_name' => "N\A",
               'quantity' => $received->quantity,
               'value' => $received->value,
               'date' => $received->created_at // Standardized date field
            ];
         });

      $mergedResults = $stock_data->concat($transactions)->concat($reversed_transactions)->concat($received_goods);

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
      return response()->json($paginator);;

      // return $results;
   }


   public function addProductCategories(Request $request)
   {
      $request->validate([
         'name' => 'required|string|unique:product_categories,name',
      ]);

      $product_categories = new ProductCategories;
      $product_categories->name = $request->name;
      $product_category_save = $product_categories->save();

      if ($product_category_save) {
         $resArr['message'] = "Successfully added product category";
         return response()->json($resArr, 200);
      } else {
         $resArr['message'] = "An error has occurred";
         return response()->json($resArr, 202);
      }
   }

   public function getWarehouseInventory(Request $request, String $id)
   {
      $ware_house_id = $id;


      if ($request->has('keyword')) {
         $keyword = $request->query('keyword');
         $res = Product::where('name', 'like', "%$keyword%")
            ->orWhere('origin', 'like', "%$keyword%")
            ->orWhereHas('category', function ($query) use ($keyword) {
               $query->where('name', 'like', "%$keyword%");
            })->with(['warehouses' => function ($query) use ($ware_house_id) {
               $query->where('ware_house_id', $ware_house_id);
            }, "category"])->get();
         return $this->paginate($res);
      } else {
         $res = Product::with(['warehouses' => function ($query) use ($ware_house_id) {
            $query->where('ware_house_id', $ware_house_id);
         }, "category"])->get();
         return $this->paginate($res);
      }
   }

   public function getAllProducts(String $id)
   {
      $ware_house_id = $id;
      $res = Product::with(['warehouses' => function ($query) use ($ware_house_id) {
         $query->where('ware_house_id', $ware_house_id);
      }, "category"])->get();
      return response()->json($res);
   }

   public function sendRequisition(Request $request)
   {
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
      if ($result) {
         foreach ($data as $item) {
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
         return response()->json($resArr, 200);
      } else {
         $resArr['message'] = 'Requisition not sent, please check network';
         return response()->json($resArr, 202);
      }
   }

   public function getRequisition(String $id)
   {
      return $this->paginate(Requisition::where('warehouse_id', $id)->with('approver')->latest()->get());
   }

   public function getRequisitionProducts(String $id)
   {
      return  ReceivedGoods::where('requisition_id', $id)->with(['products', 'admin'])->get();
   }

   public function rejectRequisition(String $id)
   {
      Requisition::where("id", $id)->delete();
      ReceivedGoods::where("requisition_id", $id)->delete();
      $resArr = [];
      $resArr['message'] = 'Requisition deleted';
      return response()->json($resArr, 200);
   }

   public function acceptRequisition(Request $request)
   {
      $request->validate([
         'requisition_id' => 'required|string',
         'approver_id' => 'required'
      ]);
      $id = $request->requisition_id;
      $approver_id = $request->approver_id;

      $requisition = Requisition::where("id", $id)->first();

      if ($requisition->status != "complete") {
         Requisition::where("id", $id)->update([
            "status" => "complete",
            "approver_id" => $approver_id
         ]);



         $goods = ReceivedGoods::where("requisition_id", $id)->with('products')->get();
         foreach ($goods as $item) {
            $price = $item->products->price;
            $quantity = $item->quantity;
            $value = $price * $quantity;
            $warehouse_id = $item->ware_house_id;
            $product_id = $item->product_id;

            $inventory = DB::table('products_warehouses')
               ->where('product_id', '=', $product_id)
               ->where('ware_house_id', '=', $warehouse_id)->first();

            $previous_quantity = $inventory->quantity;
            $new_quantity = $previous_quantity + $quantity;

            $new_value = $new_quantity * $price;

            DB::table('products_warehouses')
               ->where('product_id', '=', $product_id)
               ->where('ware_house_id', '=', $warehouse_id)->update([
                  "quantity" =>  $new_quantity,
                  "value" => $new_value
               ]);
         }

         $resArr['message'] = 'Requisition accepted';
         return response()->json($resArr, 200);
      } else {
         $resArr['message'] = 'Requisition already accepted';
         return response()->json($resArr, 200);
      }
   }

   public function getTransactions(Request $request)
   {
      $query = Transaction::query();
      if ($request->has('keyword')) {
         $keyword = $request->input('keyword');
      } else {
         $keyword = "";
      }

      if ($request->has('customer_id')) {
         $query->where('customer_id', $request->input('customer_id'));
      }

      if ($request->has('type')) {
         $date = $request->input('date');
         $type = $request->input('type');

         if ($type == "Transaction Date") {
            $query->where('transaction_date', $date);
         } else {
            $query->whereDate('created_at', $date);
         }
      }
      //Check for status params
      if ($request->has('status')) {
         $status = $request->input('status');
         $query->where('status', $status);
      }

      $transaction = $query->select(
         'invoice_no',
         'transaction_type',
         'customer_name',
         'customer_id',
         'transaction_date',
         'ware_house_name',
         'discount',
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
      })->groupBy('invoice_no', 'transaction_type', 'customer_name', 'customer_id', 'transaction_date', 'status', 'ware_house_name', 'discount')->orderByDesc('latest_created_at')->get();


      return $this->paginate($transaction);
   }

   public function getCustomers(Request $request)
   {
      if ($request->has('keyword')) {
         $keyword = $request->query('keyword');
         $result = Customer::where('name', 'like', "%$keyword%")
            ->orWhere('location', 'like', "%$keyword%")
            ->orWhere('phone', 'like', "%$keyword%")
            ->get();
         return $this->paginate($result);
      } else {
         return $this->paginate(Customer::all());
      }
   }

   public function addCustomer(Request $request)
   {
      $request->validate([
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
      return response()->json($resArr, 200);
   }

   public function getAllCustomers()
   {
      return Customer::all();
   }
   public function makeTransaction(Request $request)
   {

      $request->validate([
         'data' => 'required',
         'warehouse_id' => 'required',
         'user_id' => 'required',
         'transaction_date' => 'required',
         'transaction_type' => 'required'
      ]);
      $data = $request->data;
      $warehouse = $request->warehouse_id;
      $user_id = $request->user_id;
      $customer_id = $request->customer_id;
      $discount = $request->discount;
      $code = $request->code;
      $customer_name = $request->customer_name;
      $transaction_date = $request->transaction_date;
      $transaction_type = $request->transaction_type;
      $warehouse_location = $request->warehouse_location;
      $invoice_number = $request->invoice_no;

      if ($transaction_type == "Credit Sale") {
         $status = "unpaid";
      } else {
         $status = "paid";
      }

      $error_message = "";
      $error_counter = 0;
      $total = 0;

      if ($invoice_number == null) {
         $last_transaction = Transaction::latest()->first();
         $id = $last_transaction ? (int)$last_transaction->invoice_no : 0;
         $invoice_number = str_pad($id + 1, 6, '0', STR_PAD_LEFT);
      }




      $last_transaction = Transaction::where('invoice_no', $invoice_number)->first();
      $check_existence = Transaction::where('code', $code)->get();
      if ($check_existence->isNotEmpty()) {
         $resArr['message'] = 'Transaction already exists';
         // $resArr['transaction'] = $check_existence;
         return response()->json($resArr, 200);
      } else {
         foreach ($data as $item) {
            //DB::table('products_warehouses')
            $res = DB::table('products_warehouses')->where([
               ['ware_house_id', $warehouse],
               ['product_id', $item['product_id']]
            ])->get();

            $previous_value = $res[0]->value;
            $previous_quantity = $res[0]->quantity;

            $new_quantity = $previous_quantity - $item['quantity'];
            $new_value = $previous_value - $item['value'];

            if ($new_quantity >= 0) {

               $update = DB::table('products_warehouses')->where([
                  ['ware_house_id', $warehouse],
                  ['product_id', $item['product_id']]
               ])->update([
                  'quantity' =>  $new_quantity,
                  'value' => $new_value
               ]);
               $total = $total + $item['value'];

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
               $goods->code = $code;
               $goods->status = $status;
               $goods->discount = $discount;

               // Set the created_at timestamp to match the last transaction's created_at
               if ($last_transaction) {
                  $goods->created_at = $last_transaction->created_at;
                  $goods->updated_at = $last_transaction->updated_at;
               }

               $goods->save();
            }
         }

         if ($customer_id != null) {
            $customer = Customer::where("id", $customer_id)->first();
            $previous_balance = $customer->balance;
            $new_balance = $previous_balance + $total;

            //Update customer balance
            Customer::where('id', $customer_id)->update([
               'balance' =>  $new_balance,

            ]);
         }

         $resArr['message'] = 'Successfully created transaction';
         // $resArr['transaction'] = Transaction::where('invoice_no',$invoice_number)->with(['products','warehouse','admin','customer'])->get();
         return response()->json($resArr, 200);
      }
   }




   public function getTransactionDetails(String $invoice_number)
   {
      return Transaction::where('invoice_no', $invoice_number)->with(['products', 'warehouse', 'admin', 'customer'])->get();
   }

   public function assignInvoiceToCustomer(Request $request)
   {
      $request->validate([
         'invoice_number' => 'required',
         'customer_id' => 'required',
      ]);
      $invoice_number = $request->invoice_number;
      $customer_id = $request->customer_id;

      $customer = Customer::where("id", $customer_id)->first();
      $transactions = Transaction::where('invoice_no', $invoice_number)->get();

      $total = 0;
      foreach ($transactions as $obj) {
         $total = $total + $obj->value;
         Transaction::where('id', $obj->id)
            ->update([
               'customer_id' => $customer_id,
               'customer_name' => $customer->name
            ]);
      }

      $customer_new_balance = $customer->balance + $total;
      Customer::where('id', $customer_id)->update([
         "balance" => $customer_new_balance
      ]);

      $resArr['message'] = 'Successfully assigned customer to invoice';
      return response()->json($resArr, 200);
   }

   public function reverseTransaction(Request $request)
   {
      $request->validate([
         'invoiceNumber' => 'required',
      ]);

      $invoice_number = $request->invoiceNumber;

      $transaction = Transaction::where('invoice_no', $invoice_number)->with('products')->get();
      $customer_id = $transaction[0]->customer_id;
      $total = 0;
      foreach ($transaction as $res) {
         $product_unit_price = $res->products->price;
         $total = $total + $res->value;
         //$quantity = $res->quantity;

         //Get stock count
         $stock =  DB::table('products_warehouses')->where([
            ['ware_house_id', $res->ware_house_id],
            ['product_id', $res->product_id]
         ])->first();

         $previous_quantity = $stock->quantity;
         $new_quantity = $previous_quantity + $res->quantity;
         $new_value = $product_unit_price * $new_quantity;

         DB::table('products_warehouses')->where([
            ['ware_house_id', $res->ware_house_id],
            ['product_id', $res->product_id]
         ])->update([
            'quantity' =>  $new_quantity,
            'value' => $new_value
         ]);
      }
      if ($customer_id != null) {
         $customer = Customer::where('id', $customer_id)->first();
         $previous_balance = $customer->balance;
         $new_balance = $previous_balance - $total;

         Customer::where('id', $customer_id)->update(
            [
               "balance" => $new_balance
            ]
         );
      }
      Transaction::where('invoice_no', $invoice_number)->update([
         "status" => "reversed"
      ]);
      $resArr['message'] = 'Successfully reversed transaction';
      return response()->json($resArr, 200);
   }

   public function getInvoicePayment(String $invoice_id)
   {
      return Payment::where('invoice_no', $invoice_id)->with('admin')->get();
   }

   public function makePaymentToInvoice(Request $request)
   {
      $request->validate([
         'invoice_number' => 'required',
         'payment_type' => 'required',
         'amount' => 'required',
         'user_id' => 'required',
         'customer_id' => 'required',
         'total' => 'required',
         'details' => 'required'
      ]);

      $amount_expected = $request->total;
      //Check total payments


      $payment = new Payment;
      $payment->user_id = $request->user_id;
      $payment->invoice_no = $request->invoice_number;
      $payment->amount = $request->amount;
      $payment->type = $request->payment_type;
      $payment->details = $request->details;
      $payment->save();


      //
      $amount_payed = Payment::where('invoice_no', $request->invoice_number)->sum('amount');
      $amount_left = $amount_expected  - $amount_payed;
      if ($amount_left == 0) {
         Transaction::where('invoice_no', $request->invoice_number)
            ->update([
               'status' => "paid"
            ]);
      }




      $customer = Customer::where('id', $request->customer_id)->first();
      $previous_balance = $customer->balance;
      $balance = $previous_balance - $request->amount;

      Customer::where('id', $request->customer_id)
         ->update([
            'balance' => $balance
         ]);

      $resArr['message'] = 'Successfully created customer';
      return response()->json($resArr, 200);
   }
}
