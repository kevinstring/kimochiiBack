<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class VentasController extends Controller
{
    //
    public function getProductosEnStock(Request $request){
        $id_categoria=$request->idcategoria;


        $productos= DB::TABLE("PRODUCTO")->where('ID_CATEGORIA',$id_categoria)->get();
    
        if($productos){
            return response()->json(['success' => true, 'productos' => $productos], 200);

    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron productos'], 200);

    }}

 
    

    public function postVenta(Request $request){
        $fecha=$request->fecha;
        $nombreCliente=$request->nombreCliente;
        $tipoPago=$request->tipoPago;
        $esEnvio=$request->esEnvio;

        if($esEnvio==true){
            $esEnvio=1;
        }else{
            $esEnvio=0;
        }


        $venta= DB::TABLE("FACTURA")->insertGetid([
            'FECHA'=>$fecha,
            'NOMBRE_CLIENTE'=>$nombreCliente,
            'ID_TIPO_PAGO'=>$tipoPago,
            'ES_ENVIO'=>$esEnvio,
        ]);


        if($venta){
            return response()->json(['success' => true, 'venta' => $venta,'message'=>"Venta agregada"], 200);
        }else{
            return response()->json(['success' => false, 'message' => 'No se pudo agregar la venta'], 200);
        }


    }

    public function getVentas(Request $request){
        $ventas= DB::TABLE("VENTAS")->get();

        if($ventas){
            return response()->json(['success' => true, 'ventas' => $ventas], 200);

    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron ventas'], 200);

    }}

    public function postComanda(Request $request){
        $idVenta= $request->idVenta;
        $codigoProducto= $request->codigoProducto;
        $cantidad=$request->cantidad;

        $productoRepetido= DB::TABLE("VENTA")->where('ID_FACTURA',$idVenta)->where('CODIGO_PRODUCTO',$codigoProducto)->first();

        if($productoRepetido){
            $comanda= DB::TABLE("VENTA")->where('ID_FACTURA',$idVenta)->where('CODIGO_PRODUCTO',$codigoProducto)->update([
                'CANTIDAD'=>$cantidad,
            ]);
            $productos= DB::TABLE("PRODUCTO")->where('CODIGO',$codigoProducto)->first();

            $nuevaCantidad=$productos->CANTIDAD-$cantidad;

            $productoActualizado= DB::TABLE("PRODUCTO")->where('CODIGO',$codigoProducto)->update([
                'CANTIDAD'=>$nuevaCantidad,
            ]);

        

            if($comanda){
                return response()->json(['success' => true, 'comanda' => $comanda,'message'=>"Comanda agregada"], 200);
            }else{
                return response()->json(['success' => false, 'message' => 'No se pudo agregar la comanda'], 200);
            }
        }else{
            $comanda= DB::TABLE("VENTA")->insert([
                'ID_FACTURA'=>$idVenta,
                'CODIGO_PRODUCTO'=>$codigoProducto,
                'CANTIDAD'=>$cantidad,
            ]);
    
            if($comanda){
                return response()->json(['success' => true, 'comanda' => $comanda,'message'=>"Comanda agregada"], 200);
            }else{
                return response()->json(['success' => false, 'message' => 'No se pudo agregar la comanda'], 200);
            }
        }




    }

}
