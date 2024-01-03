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

    public function getVentas(Request $request)
    {
        $indice = $request->indice;
        $indice = (int) $indice;
        
        if ($indice === 0) {
           
            // Consulta de ventas por MES
      
            $ventas = DB::table('VENTA')
            ->join('FACTURA', 'VENTA.id_factura', '=', 'FACTURA.id_factura')
            ->whereMonth('FACTURA.Fecha', date('m'))
            ->select(DB::raw('DATE(FACTURA.Fecha) as fecha'), DB::raw('SUM(VENTA.total) as total_ventas'))
            ->groupBy(DB::raw('DATE(FACTURA.Fecha)'))
            ->get();
    
            if ($ventas) {
                return response()->json(['success' => true, 'ventas' => $ventas], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'No se encontraron ventas'], 200);
            }
        } if ($indice === 1) {
            // Consulta de ventas por SEMANA
            $ventas = DB::table('VENTA')
                ->join('FACTURA', 'VENTA.ID_FACTURA', '=', 'FACTURA.ID_FACTURA')
                ->whereBetween('FACTURA.FECHA', [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))])
                ->get();
    
            if ($ventas) {
                return response()->json(['success' => true, 'ventas' => $ventas], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'No se encontraron ventas'], 200);
            }
        } if ($indice === 2) {
            // Consulta de ventas por DIA
            $ventas = DB::table('VENTA')
                ->join('FACTURA', 'VENTA.ID_FACTURA', '=', 'FACTURA.ID_FACTURA')
                ->whereDate('FACTURA.FECHA', date('Y-m-d'))
                ->get();
    
            if ($ventas) {
                return response()->json(['success' => true, 'ventas' => $ventas], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'No se encontraron ventas'], 200);
            }
        }
    }

    public function postComanda(Request $request){
        $idVenta= $request->idVenta;
        $codigoProducto= $request->codigoProducto;
        $cantidad=$request->cantidad;

        $productoRepetido= DB::TABLE("VENTA")->where('ID_FACTURA',$idVenta)->where('CODIGO_PRODUCTO',$codigoProducto)
        ->leftjoin('PRODUCTO as product','product.CODIGO','=','VENTA.CODIGO_PRODUCTO')->first();

        if($productoRepetido){
            
            $comanda= DB::TABLE("VENTA")->where('ID_FACTURA',$idVenta)->where('CODIGO_PRODUCTO',$codigoProducto)->update([
                'CANTIDAD'=>$cantidad,
            ]);

            $productos= DB::TABLE("PRODUCTO")->where('CODIGO',$codigoProducto)->first();

          


            $totalVenta=$productos->PRECIO*$cantidad;

            $ventaActualizada= DB::TABLE("VENTA")->where('ID_FACTURA',$idVenta)->where('CODIGO_PRODUCTO',$codigoProducto)->update([
                'TOTAL'=>$totalVenta,
            ]);



            if($productos->CANTIDAD<=0){
                return response()->json(['success' => false, 'message' => 'No hay suficientes productos en stock'], 200);
            }else{
                $nuevaCantidad=$productos->CANTIDAD-1;
                $productoActualizado= DB::TABLE("PRODUCTO")->where('CODIGO',$codigoProducto)->update([
                    'CANTIDAD'=>$nuevaCantidad,
                ]);
         
            }
  
   


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
            $productos= DB::TABLE("VENTA")->where('ID_FACTURA',$idVenta)->where('CODIGO_PRODUCTO',$codigoProducto)
            ->leftjoin('PRODUCTO as product','product.CODIGO','=','VENTA.CODIGO_PRODUCTO')->first();
    
            $totalVenta=$productos->PRECIO*$cantidad;

            $ventaActualizada= DB::TABLE("VENTA")->where('ID_FACTURA',$idVenta)->where('CODIGO_PRODUCTO',$codigoProducto)->update([
                'TOTAL'=>$totalVenta,
            ]);



            if($productos->CANTIDAD<=0){
                return response()->json(['success' => false, 'message' => 'No hay suficientes productos en stock'], 200);
            }else{
                $nuevaCantidad=$productos->CANTIDAD-$cantidad;
                $productoActualizado= DB::TABLE("PRODUCTO")->where('CODIGO',$codigoProducto)->update([
                    'CANTIDAD'=>$nuevaCantidad,
                ]);
         
            }


            if($comanda){
                return response()->json(['success' => true, 'comanda' => $comanda,'message'=>"Comanda agregada"], 200);
            }else{
                return response()->json(['success' => false, 'message' => 'No se pudo agregar la comanda'], 200);
            }

            
        }




    }

    public function finalizarVenta(Request $request){
        $idVenta= $request->idVenta;

        $venta= DB::TABLE("VENTA")->where('ID_FACTURA',$idVenta)->get();

        $totalVenta=0;

        foreach($venta as $item){
            $totalVenta=$totalVenta+$item->TOTAL;
        }

        $ventaActualizada= DB::TABLE("FACTURA")->where('ID_FACTURA',$idVenta)->update([
            'TOTAL'=>$totalVenta,
        ]);

        if($ventaActualizada){
            return response()->json(['success' => true, 'message' => 'Venta finalizada'], 200);

        }else{

            return response()->json(['success' => false, 'message' => 'No se pudo finalizar la venta'], 200);

        }


    }

    public function cancelarVentaYFacturar(Request $request){
        $idVenta = $request->idVenta;
    
        $venta = DB::table("VENTA")->where('ID_FACTURA', $idVenta)->get();
    
        foreach ($venta as $item) {
            // Corrección en la actualización de la cantidad
            $restaurarCantidad = DB::table("PRODUCTO")->where('CODIGO', $item->CODIGO_PRODUCTO)->increment('CANTIDAD', $item->CANTIDAD);
        }
    
        DB::table("VENTA")->where('ID_FACTURA', $idVenta)->delete();
        DB::table("FACTURA")->where('ID_FACTURA', $idVenta)->delete();
    
        if ($restaurarCantidad) {
            return response()->json(['success' => true, 'message' => 'Venta cancelada'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No se pudo cancelar la venta'], 200);
        }
    }
    
}
