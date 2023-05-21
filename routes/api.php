<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'auth'], function(){
    Route::post('register',[UserController::class,'register']);
    Route::post('login',[UserController::class,'login']);
});

Route::group(['prefix' => 'user'], function(){
    Route::get('getData/{id}',[UserController::class,'getData']);
    Route::patch('updateData/{id}',[UserController::class,'updateData']);
    Route::post('updatePhoto/{id}',[UserController::class,'updatePhoto']);
    Route::get('getServices',[ServiceController::class,'getServices']);
    Route::get('getCompanyServices/{id}',[ServiceController::class,'getCompanyServices']);
    Route::post('addToFavourite',[UserController::class,'addFavourite']);
    Route::get('myFavourites/{id}',[UserController::class,'getFavourites']);
    Route::get('getServiceLocation/{id}',[ServiceController::class,'getServiceLocation']);
    Route::get('search/{name}',[UserController::class,'search']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
