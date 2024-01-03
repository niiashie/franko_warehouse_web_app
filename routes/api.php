<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register',[UserController::class,'registration']);
Route::post('/login',[UserController::class,'login']);
Route::middleware('auth:api')->get('/managedWareHouses',[UserController::class,'managedWareHouses']);
Route::middleware('auth:api')->post('/warehouseDetails',[UserController::class,'warehouseDetails']);
Route::middleware('auth:api')->post('/confirmRequisition',[UserController::class,'confirmRequisition']);
Route::middleware('auth:api')->post('/transactionDetails',[UserController::class,'transactionDetails']);
Route::middleware('auth:api')->post('/receivedGoodsDetails',[UserController::class,'receivedGoodsDetails']);
Route::middleware('auth:api')->post('/inventoryDetails',[UserController::class,'getInventory']);
Route::middleware('auth:api')->get('/requisitionNotification',[UserController::class,'getRequisitionNotification']);
Route::middleware('auth:api')->get('/accountants',[UserController::class,'getAccountants']);
Route::middleware('auth:api')->post('/submitStock',[UserController::class,'processStock']);
Route::middleware('auth:api')->get('/getConfirmedStock',[UserController::class,'getConfirmedStocks']);
Route::middleware('auth:api')->post('/approveStock',[UserController::class,'approveStock']);
