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
Route::post('cancelarVentaYFacturar','App\Http\Controllers\VentasController@cancelarVentaYFacturar');
Route::post('finalizarVenta','App\Http\Controllers\VentasController@finalizarVenta');
Route::post('getVentas','App\Http\Controllers\VentasController@getVentas');
Route::post('getComprasPedidos','App\Http\Controllers\PedidosController@getComprasPedidos');
Route::post('cambiarEstadoPedido','App\Http\Controllers\PedidosController@cambiarEstadoPedido');
Route::post('retrasarEstadoPedido','App\Http\Controllers\PedidosController@retrasarEstadoPedido');
Route::post('pedidoAtrasadoDeTiempo','App\Http\Controllers\PedidosController@pedidoAtrasadoDeTiempo');
Route::get('getSatisfaccion','App\Http\Controllers\PedidosController@getSatisfaccion');
Route::post('valorarPedido ','App\Http\Controllers\PedidosController@valorarPedido');
Route::get('getPedidos','App\Http\Controllers\PedidosController@getPedidos');
Route::get('getRopa','App\Http\Controllers\articulosController@getRopa');
Route::get('getProveedoresInter','App\Http\Controllers\webController@getProveedoresInter');
Route::get('getCategoriasW','App\Http\Controllers\webController@getCategoriasW');
Route::post('getOneCategory','App\Http\Controllers\webController@getOneCategory');
Route::post('buscar','App\Http\Controllers\webController@buscar');
Route::get('getMasVendidos','App\Http\Controllers\webController@getMasVendidos');
Route::post('getDetalleProducto','App\Http\Controllers\webController@getDetalleProducto');
Route::post('getRecomendados','App\Http\Controllers\webController@getRecomendados');