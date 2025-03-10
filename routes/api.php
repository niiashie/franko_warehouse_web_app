<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebController;
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
Route::middleware('auth:sanctum')->delete('/deleteStock',[UserController::class,'deleteStock']);
Route::middleware('auth:sanctum')->post('/getStockCategoryDetails',[UserController::class,'getStockCategoryDetails']);
Route::middleware('auth:sanctum')->post('/updatePendingStockCount',[UserController::class,'updatePendingStockCount']);


//API's for web app

Route::post('/web/login',[WebController::class,'login']);
Route::middleware('auth:sanctum')->get('web/getDashboardValues/{id}',[WebController::class,'getDashboardValues']);
Route::middleware('auth:sanctum')->get('web/getCategoryProducts/{category_id}/{warehouse_id}',[WebController::class,'getCategoryProducts']);
Route::middleware('auth:sanctum')->get('web/getWarehouse',[WebController::class,'getWarehouse']);
Route::middleware('auth:sanctum')->post('web/addWarehouse',[WebController::class,'addWarehouse']);
Route::middleware('auth:sanctum')->post('web/changeRoles',[WebController::class,'changeRoles']);
Route::middleware('auth:sanctum')->post('web/changeStaffStatus',[WebController::class,'changeStaffStatus']);
Route::middleware('auth:sanctum')->post('web/assignStaff',[WebController::class,'assignStaff']);
Route::middleware('auth:sanctum')->get('web/getUnassignedStaff/{id}',[WebController::class,'getUnassignedStaff']);
Route::middleware('auth:sanctum')->get('web/getProduct',[WebController::class,'getProduct']);
Route::middleware('auth:sanctum')->get('web/getProductCategories',[WebController::class,'getProductCategories']);
Route::middleware('auth:sanctum')->post("web/createProduct",[WebController::class,'createProduct']);
Route::middleware('auth:sanctum')->patch('web/updateProduct/{id}', [WebController::class, 'updateProduct']);
Route::middleware('auth:sanctum')->post('web/getProductMovement',[WebController::class,'getProductMovement']);

