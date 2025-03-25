<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/',[PageController::class,'login'])->name('loginPage');
Route::get('/home',[PageController::class,'home'])->name('homePage');
Route::get('/aboutUs',[PageController::class,'aboutUs'])->name('aboutUsPage');
Route::get('/products',[PageController::class,'products'])->name('productsPage');
Route::get('/inventory',[PageController::class,'inventory'])->name('inventoryPage');
Route::get('/warehouse',[PageController::class,'warehouse'])->name('warehousePage');
Route::get('/stock',[PageController::class,'stock'])->name('stock');
Route::get('/products/{id}',[PageController::class,'productDetail2']);
Route::get('/productCategoty/{category_name}',[PageController::class,'productCategoryInfo']);
Route::get('/productDetail',[PageController::class,'productDetail']);
Route::get('/transactions',[PageController::class,'transaction'])->name('transactionPage');
Route::get('/leastQuantites',[PageController::class,'leastQuantities']);
Route::get('/accountant',[PageController::class,'accountant']);
Route::get('/profermer',[PageController::class,'proformer'])->name('profermerPage');

Route::post('/loginData',[PageController::class,'loginData'])->name('loginData');
Route::post('/saveData',[PageController::class,'saveData'])->name('saveData');



//Ware House 
Route::post('/addWarehouse',[WarehouseController::class,'addWarehouse'])->name('addWarehouse');
Route::get('/getWarehouseRoles',[WarehouseController::class,'getWarehouseRoles']);
Route::get('/getWarehouse',[WarehouseController::class,'getWarehouse']);
Route::get('/deleteWarehouse',[WarehouseController::class,'deleteWarehouses'])->name('deleteWareHouse');
Route::get('/confirmRegistration',[WarehouseController::class,'confirmRegistration']);
Route::get('/assignStaffToWarehouse',[WarehouseController::class,'assignStaffToWarehouse']);
Route::get('/assignManagerToWarehouse',[WarehouseController::class,'assignManagerToWarehouse']);
Route::get('/changeRoles',[WarehouseController::class,'changeRoles']);

//Products
Route::get('/addProducts',[ProductController::class,'addProducts']);
Route::post('/addProductCategories',[ProductController::class,'addProductCategories'])->name('addProductCategories');
Route::get('/getProductCategories',[ProductController::class,'getProductCategories']);
Route::get('/getProducts',[ProductController::class,'getProducts2']);
Route::get('/deleteCategory',[ProductController::class,'deleteCategory']);
Route::get('/updateCategory',[ProductController::class,'updateCategory']);
Route::get('/deleteProduct',[ProductController::class,'deleteProduct']);
Route::get('/updateProduct',[ProductController::class,'updateProduct']);

//Inventory
Route::get('/receiveGoods',[InventoryController::class,'receiveGoods']);
Route::get('/inventoryHistory',[InventoryController::class,'inventoryHistory']);
Route::get('/requestRequisition',[InventoryController::class,'requestRequisition']);

//Transaction
Route::get('/transact',[TransactionController::class,'transact']);
Route::get('/reverse',[TransactionController::class,'reverse']);
Route::get('/todayTransaction',[TransactionController::class,'todayTrans']);
Route::get('/checkApi',[UserController::class,'managedWareHouses']);
Route::get('/productList',[PageController::class,'getProductList']);
Route::get('/productStockList',[PageController::class,'getProductStock']);
Route::post('/updateStock',[PageController::class,'updateStock']);
Route::post('/transactionHistory',[TransactionController::class,'getTransaction'])->name('transactionHistory');;

//Home
Route::get('/homeValues',[PageController::class,'homeValues']);

//Stock
Route::get('/submitStock',[PageController::class,'submitStock']);
Route::get('/stockApprovalTest',[PageController::class,'stockApprovalTest']);

//Test
Route::get('/test',[PageController::class,'test']);




