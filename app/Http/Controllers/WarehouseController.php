<?php

namespace App\Http\Controllers;

use App\Models\WareHouseStaff;
use Illuminate\Http\Request;
use App\Models\WareHouse;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
class WarehouseController extends Controller
{
   
    public function getWarehouse(){
        
        $warehouse_managers = WarehouseStaff::where('role','Manager')->whereHas('warehouse',function($query){
            $query->where('wstatus', 'active');
        })->with(['admin','warehouse'])->latest()->get(); ;
        
        $active_members = WareHouseStaff::where('status', 'active')->whereHas('warehouse', function($query){
            $query->where('wstatus', 'active');
            })->with(['admin','warehouse'])->latest()->get(); 

        $inactive_members = WareHouseStaff::where('status','inactive')->with('admin')->get();

        $managed_warehouse = WareHouse::where('wstatus','active')->whereHas('staff',function($query){
           $query->where('role','Manager');
        })->get();


        //Getting unManagedWarehouse
        //Get all warehouses
        $all_warehouses = WareHouse::where('wstatus','active')->get();
        
        $un_managed_ware_house = [];

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
            ['status',"=","active"]
        ])->with('admin')->get();

        

        return view('warehouse',[
             'warehouse_managers' => $warehouse_managers,
             'active_members'=> $active_members,
             'inactive_members'=> $inactive_members,
             'managed_warehouse' => $managed_warehouse,
             'unmanaged_warehouse' => $un_managed_ware_house,
             'unassigned_staff' => $unassigned_staff,
             'warehouses' => $all_warehouses
            ]
        );

        //return $active_members;//$warehouse_managers;

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

            return back()->with('ware_house_addition', $result2);

         }else{
            return back()->withErrors(['Please check Internet connection', '']);
         }


    }

    public function deleteWarehouses(Request $request){
       //return $request->id;
       $warehouseId = $request->id;
         
        
            DB::table('ware_houses')
                  ->where('id', $warehouseId)
                  ->update(['wstatus' => "inactive"]);
            echo "Delete Successful";
        
       
    }

    public function confirmRegistration(Request $request){
        $staffId = $request->staff_id;
        DB::table('ware_house_staff')
        ->where('uid', $staffId)
        ->update([
            'status' => "active",
            'role'=>"Staff"
            ]);
        echo "Success";
       
       
    }

    public function assignStaffToWarehouse(Request $request){
        
        $staffId = $request->staff_id;
        $role = $request->role;
        $warehouseId = $request->warehouse_id;

       

        if($role == "Manager"){
            //$test = DB::select("Select * from ware_house_staffs where ware_house_id='$warehouseId' and role='Manager'");
            $test = WareHouseStaff::where([
                ['ware_house_id',$warehouseId],
                ['role','Manager']
            ])->get();

            if(count($test)==0){
              DB::table('ware_house_staff')
                ->where('uid', $staffId)
                ->update([
                    'ware_house_id' => $warehouseId,
                    'role'=>"Manager"
                    ]);
                echo "Success";    
            }else{
                echo "Ware house already assigned to a manager";
            }
        }else{
        

            DB::table('ware_house_staff')
            ->where('uid', $staffId)
            ->update([
                'ware_house_id' => $warehouseId,
                'role'=> $role
                ]);
            echo "Success" ; 
        }
    }

    public function assignManagerToWarehouse(Request $request){
       $staffId = $request->staff_id;
       $warehouseId = $request->ware_house_id;
    
       $test = DB::select("Select * from ware_house_staffs where staff_id = '$staffId' and ware_house_id = 0");
       if(count($test) != 0){
            DB::table('ware_house_staffs')
            ->where('id', $staffId)
            ->update([
                'ware_house_id' => $warehouseId,
                'role'=> 'Manager'
                ]);
            echo "Success" ; 
       }else{
           echo "Staff already assigned to another ware house";
       }

    }

    public function changeRoles(Request $request){
       $staffId = $request->staff_id;
       $role = $request->role;
       $warehouseId = $request->warehouse_id;

       if($role == "Manager"){
            //$test = DB::select("Select * from ware_house_staffs where ware_house_id='$warehouseId' and role='Manager'");
            $test = WareHouseStaff::where([
                ['ware_house_id',$warehouseId],
                ['role','Manager']
            ])->get();

            if(count($test)==0){
            DB::table('ware_house_staff')
                ->where('uid', $staffId)
                ->update([
                    'role'=> $role
                    ]);
                $currentId = session('id');
        
                if($currentId == $staffId){
                    session([ 'role' => $role]);
                }            
                echo "Success";    
            }else{
                echo "Ware house already assigned to a manager";
            }
       }
       else{
            DB::table('ware_house_staff')
            ->where('uid', $staffId)
            ->update([
                'role'=> $role
            ]);

            $currentId = session('id');
            
            if($currentId == $staffId){
                session([ 'role' => $role]);
            }        
            echo "Success" ;
       }
    }
}
