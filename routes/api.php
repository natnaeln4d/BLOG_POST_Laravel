<?php

use App\Http\Controllers\Authcontroller;
use App\Http\Controllers\PostController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::resource('post',PostController::class)->middleware('auth:sanctum');
Route::post('auth/registerr',[Authcontroller::class,'createuser']);
Route::post('auth/loginn',[Authcontroller::class,'loginuser']);
Route::post('auth/logoutt',[Authcontroller::class,'logout']);
Route::post('auth/foregotpassword',[Authcontroller::class,'foregotpwd']);
Route::post('auth/passwordreset',[Authcontroller::class,'reset']);
