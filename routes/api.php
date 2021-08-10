<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::GET('/offers', [\App\Http\Controllers\OfferController::class,'index']);
Route::GET('/offers/publish', [\App\Http\Controllers\OfferController::class,'publish']);
Route::prefix('checking')->group(function () {
    Route::POST('pelando', [\App\Http\Controllers\OfferController::class,'pelando']);
});