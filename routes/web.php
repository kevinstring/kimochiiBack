<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\articulosController;
use App\Http\Controllers\proveedoresController;
use App\Http\Controllers\VentasController;
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
Route::post('postTag','App\Http\Controllers\articulosController@postTag');
Route::get('getCategorias','App\Http\Controllers\articulosController@getCategorias');
Route::post('asignarSubCategoria','App\Http\Controllers\articulosController@asignarSubCategoria');
Route::post('getSubCategorias','App\Http\Controllers\articulosController@getSubCategorias');
Route::post('updateAmazon','App\Http\Controllers\amazons3@store');
Route::post('guardarProducto','App\Http\Controllers\articulosController@guardarProducto');
Route::post('postFavorito','App\Http\Controllers\articulosController@postFavorito');
Route::post('getFavoritos','App\Http\Controllers\articulosController@getFavoritos');
Route::get('getProveedores','App\Http\Controllers\proveedoresController@getProveedores');
Route::post('postProveedores','App\Http\Controllers\proveedoresController@postProveedores');
Route::post('getCompras','App\Http\Controllers\proveedoresController@getCompras');
Route::post('deleteProveedores','App\Http\Controllers\proveedoresController@deleteProveedores');
Route::post('postCompras','App\Http\Controllers\proveedoresController@postCompras');
Route::post('registrarVenta','App\Http\Controllers\VentasController@postVenta');
Route::post('getProductosEnStock','App\Http\Controllers\VentasController@getProductosEnStock');
Route::post('postComanda','App\Http\Controllers\VentasController@postComanda');