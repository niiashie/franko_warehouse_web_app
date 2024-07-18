<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/test',function(Request $request){
     return 'Authenticated';
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register',[UserController::class,'registration']);
Route::post('/login',[UserController::class,'login']);
Route::middleware('auth:sanctum')->get('/managedWareHouses',[UserController::class,'managedWareHouses']);
Route::middleware('auth:sanctum')->post('/warehouseDetails',[UserController::class,'warehouseDetails']);
Route::middleware('auth:sanctum')->post('/confirmRequisition',[UserController::class,'confirmRequisition']);
Route::middleware('auth:sanctum')->post('/transactionDetails',[UserController::class,'transactionDetails']);
Route::middleware('auth:sanctum')->post('/receivedGoodsDetails',[UserController::class,'receivedGoodsDetails']);
Route::middleware('auth:sanctum')->post('/inventoryDetails',[UserController::class,'getInventory']);
Route::middleware('auth:sanctum')->get('/requisitionNotification',[UserController::class,'getRequisitionNotification']);
Route::middleware('auth:sanctum')->get('/accountants',[UserController::class,'getAccountants']);
Route::middleware('auth:sanctum')->post('/submitStock',[UserController::class,'processStock']);
Route::middleware('auth:sanctum')->get('/getConfirmedStock',[UserController::class,'getConfirmedStocks']);
Route::middleware('auth:sanctum')->post('/approveStock',[UserController::class,'approveStock']);
Route::middleware('auth:sanctum')->get('/getCategoryProducts/{id}',[ProductController::class,'getCategoryProducts']);
Route::middleware('auth:sanctum')->get('/productCategories',[ProductController::class,'productCategorList']);
Route::middleware('auth:sanctum')->get('/getPendingStock/{id}',[UserController::class,'getPendingStock']);
Route::middleware('auth:sanctum')->post('/initiateStock',[UserController::class,'initiateStock']);
Route::middleware('auth:sanctum')->get('/getPendingStockCategories/{id}',[UserController::class,'getPendingStockCategories']);
Route::middleware('auth:sanctum')->get('/getProductCategoryStock/{id}',[UserController::class,'getProductCategoryStock']);
Route::middleware('auth:sanctum')->post('/postCategoryStockData',[UserController::class,'postCategoryStockData']);

