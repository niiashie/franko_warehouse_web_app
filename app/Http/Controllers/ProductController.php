<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductCategories;
use App\Models\Product;
use App\Models\WareHouse;

class ProductController extends Controller
{
    public function addProducts(Request $request){
       
        $product = new Product;
        $product->name = $request->name;
        $product->origin = $request->origin;
        $product->category_id = $request->category_id;
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
            echo "Success";
        }else{
            echo "Please check connections";
        }

    }



    public function getProducts(){
        
        $result = DB::select('select * from products where pstatus="active"');
        // $result = Products::where('pstatus', 'active')-with('category')->get();

        $resultLength = count($result);

        $resultArray = [];

        /*foreach($result as $row){
          
            $categoryId = $row->category_id;

            $res2 = DB::select("select * from product_categories where id = '$categoryId'");
            $categoryName = $res2->name;

            $subArray = [
                'id' => $row->id,  
                'name'=> $row->name,
                'origin'=>$row->origin,
                'price'=>$row->price,
                'category'=> $categoryName
            ];

            array_push($resultArray,$subArray);
        }*/

        $y = array_reverse($resultArray);
        echo json_encode($y);
    }

    public function getProducts2(){
         $result = Product::where('pstatus','active')->get();
        return view('testFiles.one',compact('result'));
    }

    public function addProductCategories(Request $request){
        $request ->validate([
            'categoryName' => 'required|string|unique:product_categories,name',
        ]);

        $product_categories = new ProductCategories;
        $product_categories->name = $request->categoryName;
        $product_category_save = $product_categories->save();

        if($product_category_save){
            return back()->with('categories_addition','Product category successfully added');
        }
        else{
            return back()->withErrors(['Please check Internet connection', '']);
        }

        //return $request->categoryName;
    }

    public function getProductCategories(){

        $result = DB::select('select * from product_categories');

        $resultArray = [];

        foreach($result as $row){
            $subArray = [
                'id' => $row->id,
                'name'=> $row->name
            ];
            array_push($resultArray,$subArray);
        }

        $reverseArray = array_reverse($resultArray);

        return json_encode($reverseArray);

    }

    public function deleteCategory(Request $request){
       $categoryId =  $request->id;

       ProductCategories::where('id',$categoryId)->delete();
       
       echo "Success";
    }

    public function deleteProduct(Request $request){
       $productId = $request->id;

       Product::where('id',$productId)
                ->update([
                   'pstatus'=>'inactive'
                ]);
        echo "Success";        
    }

    public function updateProduct(Request $request){
       $name = $request->name;
       $origin = $request->origin;
       $price = $request->price;
       $id = $request->id;
       $wId = session('warehouse');
       Product::where('id',$id)
                ->update(
                    [
                        'name' => $name,
                        'origin'=> $origin,
                        'price' => $price
                    ]
                );

        $quantity = DB::table('products_warehouses')->where([
                    ['product_id',$id],
                    ['ware_house_id',$wId]
                    ]
                )->pluck('quantity')->first();
       
        $value = $quantity*$price;
        
        DB::table('products_warehouses')->where(
                [
                    ['product_id',$id],
                    ['ware_house_id',$wId]
                ]) ->update(
                    [
                        'value' => $value
                    ]
                );
        echo "Success";         
    }

    public function updateCategory(Request $request){
        $categoryId =  $request->id;
        $categoryName = $request->name;

        ProductCategories::where('id',$categoryId)
                            ->update([
                                'name' => $categoryName,
                            ]);

        echo "Success";                 
    }
}
