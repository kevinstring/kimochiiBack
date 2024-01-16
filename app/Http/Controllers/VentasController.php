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
        
        if ($indice === 2) {
            $granTotal = 0;
            $ventas = DB::table('VENTA')
                ->leftJoin('FACTURA', 'VENTA.id_factura', '=', 'FACTURA.id_factura')
                ->leftJoin("PRODUCTO", "PRODUCTO.CODIGO", "=", "VENTA.CODIGO_PRODUCTO")
                ->whereMonth('FACTURA.Fecha', date('m'))
                ->select(DB::raw('DATE(FACTURA.Fecha) as fecha'), DB::raw('SUM(VENTA.total) as total_ventas') , 'PRODUCTO.NOMBRE')
                ->groupBy(DB::raw('DATE(FACTURA.Fecha)'), 'PRODUCTO.NOMBRE' )
                ->get();
        
            $ventasPorFecha = [];
        
            foreach ($ventas as $item) {
                $granTotal += $item->total_ventas;
        
                // Organizar por fecha
                $fechaVenta = date('Y-m-d', strtotime($item->fecha));
                if (!isset($ventasPorFecha[$fechaVenta])) {
                    $ventasPorFecha[$fechaVenta] = [
                        'total_ventas' => 0,
                        'detalle' => [],
                    ];
                }
        
                $ventasPorFecha[$fechaVenta]['total_ventas'] += $item->total_ventas;
        
                // Detalles de productos vendidos
                $ventasPorFecha[$fechaVenta]['detalle'][] = [
                    'producto' => $item->NOMBRE,
                    'total' => $item->total_ventas,
                    // Otros campos que puedas necesitar
                ];
            }
        
            // Estructurar la respuesta para el gráfico
            $respuestaChart = [];
        
            foreach ($ventasPorFecha as $fecha => $datos) {
                $serie = [
                    'name' => $fecha,
                    'value' => $datos['total_ventas'],
                ];
        
                $respuestaChart[] = $serie;
            }
        
            $response = [
                'success' => true,
                'granTotal' => $granTotal,
                'ventasPorFecha' => $ventasPorFecha,
                'chartData' => [
                    'name' => 'Mes',
                    'series' => $respuestaChart,
                ],
            ];
        
            return response()->json($response, 200);
        } 
         else 
        if ($indice === 1) {
            $granTotal = 0;
            $ventas = DB::table('VENTA')
                ->leftJoin('FACTURA', 'VENTA.ID_FACTURA', '=', 'FACTURA.ID_FACTURA')
                ->leftJoin("PRODUCTO", "PRODUCTO.CODIGO", "=", "VENTA.CODIGO_PRODUCTO")
                ->whereBetween('FACTURA.FECHA', [
                    date('Y-m-d', strtotime('-1 week')),  // Hace una semana desde hoy
                    date('Y-m-d')  // Hoy
                ])
                ->get();
        
            $ventasPorFecha = [];
        
            foreach ($ventas as $item) {
                $granTotal += $item->TOTAL;
        
                // Organizar por fecha
                $fechaVenta = date('Y-m-d', strtotime($item->FECHA));
                if (!isset($ventasPorFecha[$fechaVenta])) {
                    $ventasPorFecha[$fechaVenta] = [
                        'total_ventas' => 0,
                        'detalle' => [],
                    ];
                }
        
                $ventasPorFecha[$fechaVenta]['total_ventas'] += $item->TOTAL;
        
                // Detalles de productos vendidos
                $ventasPorFecha[$fechaVenta]['detalle'][] = [
                    'producto' => $item->NOMBRE,
                    'total' => $item->TOTAL,
                    // Otros campos que puedas necesitar
                ];
            }
        
            // Estructurar la respuesta para el gráfico
            $respuestaChart = [];
        
            foreach ($ventasPorFecha as $fecha => $datos) {
                $serie = [
                    'name' => $fecha,
                    'value' => $datos['total_ventas'],
                ];
        
                $respuestaChart[] = $serie;
            }
        
            $response = [
                'success' => true,
                'granTotal' => $granTotal,
                'ventasPorFecha' => $ventasPorFecha,
                'chartData' => [
                    'name' => 'Semana',
                    'series' => $respuestaChart,
                ],
            ];
        
            return response()->json($response, 200);
        }
        
         else
         if ($indice === 0) {
            $fechaHoy = date('Y-m-d');
            // Consulta de ventas por DIA
            $ventas = DB::table('VENTA')
                ->leftjoin('FACTURA', 'VENTA.ID_FACTURA', '=', 'FACTURA.ID_FACTURA')
                ->leftjoin("PRODUCTO", "PRODUCTO.CODIGO", "=", "VENTA.CODIGO_PRODUCTO")
                ->whereDate('FACTURA.FECHA', $fechaHoy)
                ->get();

                $granTotal=0;

                foreach($ventas as $item){
                    $granTotal=$granTotal+$item->TOTAL;

                }
    
            if ($ventas) {
                return response()->json(['success' => true, 'ventas' => $ventas,'granTotal'=>$granTotal], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'No se encontraron ventas'], 200);
            }
        }
    }

    public function postComanda(Request $request){
        $idVenta = $request->idVenta;
        $codigoProducto = $request->codigoProducto;
        $cantidad = $request->cantidad;
        $talla = $request->talla;
    
        // Verificar si el producto ya está en la venta
        $productoRepetido = DB::table("VENTA")
            ->where('ID_FACTURA', $idVenta)
            ->where('CODIGO_PRODUCTO', $codigoProducto)
            ->leftJoin('PRODUCTO as product', 'product.CODIGO', '=', 'VENTA.CODIGO_PRODUCTO')
            ->first();
    
        if ($productoRepetido) {
            // Actualizar la cantidad en la venta existente
            $comanda = DB::table("VENTA")
                ->where('ID_FACTURA', $idVenta)
                ->where('CODIGO_PRODUCTO', $codigoProducto)
                ->update([
                    'CANTIDAD' => $cantidad,
                ]);
    
            // Obtener información del producto
            $productos = DB::table("PRODUCTO")
                ->where('CODIGO', $codigoProducto)
                ->first();

                if($talla=="M" || $talla==="L" || $talla==="S"){
                    $ropas=DB::table("ROPA")
                    ->where('CODIGO',$codigoProducto)
                    ->where('TALLA',$talla)
                    ->first();
                }
    
            // Calcular el nuevo total de la venta
            $totalVenta = $productos->PRECIO * $cantidad;
    
            // Actualizar el total en la venta
       
            $ventaActualizada = DB::table("VENTA")
                ->where('ID_FACTURA', $idVenta)
                ->where('CODIGO_PRODUCTO', $codigoProducto)
                ->update([
                    'TOTAL' => $totalVenta,
                ]);
    
            // Verificar si hay suficientes productos en stock
            if ($productos->CANTIDAD <= 0) {
                return response()->json(['success' => false, 'message' => 'No hay suficientes productos en stock'], 200);
            } else {
                // Actualizar la cantidad de productos en stock
                $nuevaCantidad = $productos->CANTIDAD - 1;
                $productoActualizado = DB::table("PRODUCTO")
                    ->where('CODIGO', $codigoProducto)
                    ->update([
                        'CANTIDAD' => $nuevaCantidad,
                    ]);
    
                // Actualizar la cantidad de ropa en stock
                if($talla=="M" || $talla==="L" || $talla==="S"){
                    $ropaActualizada = DB::table("ROPA")
                    ->where('CODIGO_PRODUCTO', $codigoProducto)
                    ->where('TALLA', $talla)
                    ->update([
                        'CANTIDAD' => $nuevaCantidad,
                    ]);
                }

            }
    
            // Verificar si la comanda se actualizó correctamente
            if ($comanda) {
                return response()->json(['success' => true, 'comanda' => $comanda, 'message' => "Comanda actualizada"], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'No se pudo actualizar la comanda'], 200);
            }
        } else {
            // Si el producto no está en la venta, agregar una nueva entrada
            $comanda = DB::table("VENTA")->insert([
                'ID_FACTURA' => $idVenta,
                'CODIGO_PRODUCTO' => $codigoProducto,
                'CANTIDAD' => $cantidad,
            ]);
    
            $productos = DB::table("PRODUCTO")
            ->where('CODIGO', $codigoProducto)
            ->first();
        
        if ($productos) {
            // El producto fue encontrado, puedes acceder a sus propiedades
            $totalVenta = $productos->PRECIO * $cantidad;
            
            // Resto del código...
        } else {
            // El producto no fue encontrado, manejar el caso de producto no existente
            return response()->json(['success' => false, 'message' => 'Producto no encontrado'], 200);
        }
        
    
            // Actualizar el total en la venta
            $ventaActualizada = DB::table("VENTA")
                ->where('ID_FACTURA', $idVenta)
                ->where('CODIGO_PRODUCTO', $codigoProducto)
                ->update([
                    'TOTAL' => $totalVenta,
                ]);
    
            // Verificar si hay suficientes productos en stock
            if ($productos->CANTIDAD <= 0) {
                return response()->json(['success' => false, 'message' => 'No hay suficientes productos en stock'], 200);
            } else {
                // Actualizar la cantidad de productos en stock
                $nuevaCantidad = $productos->CANTIDAD - $cantidad;
                $productoActualizado = DB::table("PRODUCTO")
                    ->where('CODIGO', $codigoProducto)
                    ->update([
                        'CANTIDAD' => $nuevaCantidad,
                    ]);
    
                // Actualizar la cantidad de ropa en stock
                $ropaActualizada = DB::table("ROPA")
                    ->where('CODIGO_PRODUCTO', $codigoProducto)
                    ->where('TALLA', $talla)
                    ->update([
                        'CANTIDAD' => $nuevaCantidad,
                    ]);
            }
    
            // Verificar si la comanda se agregó correctamente
            if ($comanda) {
                return response()->json(['success' => true, 'comanda' => $comanda, 'message' => "Comanda agregada"], 200);
            } else {
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
