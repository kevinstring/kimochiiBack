<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\articulosController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');


});
Route::get('getProducto','App\Http\Controllers\articulosController@getArticulos');
Route::post('postCategoria','App\Http\Controllers\articulosController@postCategoria');
Route::post('postSubCategoria','App\Http\Controllers\articulosController@postSubCategoria');
Route::get('getCategorias','App\Http\Controllers\articulosController@getCategorias');
Route::post('asignarSubCategoria','App\Http\Controllers\articulosController@asignarSubCategoria');
Route::post('getSubCategorias','App\Http\Controllers\articulosController@getSubCategorias');
Route::post('updateAmazon','App\Http\Controllers\amazons3@store');