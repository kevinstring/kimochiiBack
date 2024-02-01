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


        if($id_categoria==22){
            $productos= DB::TABLE("PRODUCTO")->leftjoin("ROPA","ROPA.CODIGO", "=","PRODUCTO.CODIGO")->select("ROPA.T_S as TALLA_S","ROPA.T_M as TALLA_M","ROPA.T_L as TALLA_L","ROPA.T_XL as TALLA_XL","ROPA.T_10 as TALLA_10","ROPA.T_12 as TALLA_12","ROPA.T_14 as TALLA_14","PRODUCTO.CANTIDAD","PRODUCTO.CODIGO","PRODUCTO.FOTO")->where('PRODUCTO.ID_CATEGORIA',$id_categoria)->get();
        
            foreach ($productos as $producto) {
                $cantidadTallaS = $producto->TALLA_S;
                $cantidadTallaM = $producto->TALLA_M;
                $cantidadTallaL = $producto->TALLA_L;
                    $cantidadTallaXL = $producto->TALLA_XL;
                    $cantidadTalla10 = $producto->TALLA_10;
                    $cantidadTalla12 = $producto->TALLA_12;
                    $cantidadTalla14 = $producto->TALLA_14;
            
                $cantidadRopa = array_sum([$cantidadTallaS, $cantidadTallaM, $cantidadTallaL,$cantidadTallaXL,$cantidadTalla10,$cantidadTalla12,$cantidadTalla14]);
            
                // Agregar la cantidad total al objeto producto
                $producto->CANTIDAD = $cantidadRopa;
            
                // Agregar las cantidades por talla al array TALLAS dentro del objeto producto
                $producto->TALLAS = [
                    "S" => $cantidadTallaS ,
                    "M" => $cantidadTallaM,
                    "L" => $cantidadTallaL,
                    "XL" => $cantidadTallaXL,
                    "10" => $cantidadTalla10,
                    "12" => $cantidadTalla12,
                    "14" => $cantidadTalla14,

                ];
        
            }
     }else{

        $productos= DB::TABLE("PRODUCTO")->where('ID_CATEGORIA',$id_categoria)->get();

    }
    
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
            $ventas = DB::table("FACTURA")

                ->whereBetween('FACTURA.FECHA', [
                    date('Y-m-d', strtotime('-1 month')),  // Hace un mes desde hoy
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
                        'TOTAL' => 0,
                        'detalle' => [],
                    ];
                }
        
                $ventasPorFecha[$fechaVenta]['TOTAL'] += $item->TOTAL;
        
                // Detalles de productos vendidos
                $ventasPorFecha[$fechaVenta]['detalle'][] = [

                    'total' => $item->TOTAL,
                    // Otros campos que puedas necesitar
                ];
            }
        
            // Estructurar la respuesta para el gráfico
            $respuestaChart = [];
        
            foreach ($ventasPorFecha as $fecha => $datos) {
                $serie = [
                    'name' => $fecha,
                    'value' => $datos['TOTAL'],
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

            $ropas = DB::table("ROPA")
            ->where('CODIGO', $codigoProducto)
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
                if ($talla == "M" || $talla === "L" || $talla === "S" || $talla === "XL" || $talla === "10" || $talla === "12" || $talla === "14") {
   
        
                    $nombreCampo = 'T_' . $talla;
        
                    // Obtener la cantidad actual de ropa para la talla específica
                    $cantidadActual = $ropas->{$nombreCampo};
        
                    // Verificar si hay suficiente cantidad de ropa
                    if ($cantidadActual > 0) {
                        // Calcular la nueva cantidad de ropa
                        $nuevaCantidadRopa = $cantidadActual - 1;
        
                        // Actualizar la cantidad de ropa en la tabla
                        $actualizarPrenda = DB::table("ROPA")
                            ->where('CODIGO', $codigoProducto)
                            ->update([
                                $nombreCampo => $nuevaCantidadRopa,
                            ]);
                    } else {
                        // Manejar el caso donde no hay suficiente cantidad de ropa en esa talla
                        return response()->json(['success' => false, 'message' => 'No hay suficiente cantidad de ropa en la talla especificada'], 200);
                    }
                }

                // if($ropas){
                //     $cantidad=$ropas->$talla;
                // }else{
                //     $cantidad=0;
                // }

                // $actualizarRopa=DB::table("ROPA")
                // ->where('CODIGO',$codigoProducto)
                // ->where($talla,)

    
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
    
                    // if ($talla == "M" || $talla === "L" || $talla === "S" || $talla === "XL" || $talla === "10" || $talla === "12" || $talla === "14") {
                    //     $ropas = DB::table("ROPA")
                    //         ->where('CODIGO', $codigoProducto)
                    //         ->first();
            
                    //     $nombreCampo = 'T_' . $talla;
            
                    //     // Obtener la cantidad actual de ropa para la talla específica
                    //     $cantidadActual = $ropas->{$nombreCampo};
            
                    //     // Verificar si hay suficiente cantidad de ropa
                    //     if ($cantidadActual > 0) {
                    //         // Calcular la nueva cantidad de ropa
                    //         $nuevaCantidadRopa = $cantidadActual - 1;
            
                    //         // Actualizar la cantidad de ropa en la tabla
                    //         $actualizarPrenda = DB::table("ROPA")
                    //             ->where('CODIGO', $codigoProducto)
                    //             ->update([
                    //                 $nombreCampo => $nuevaCantidadRopa,
                    //             ]);
                    //     } else {
                    //         // Manejar el caso donde no hay suficiente cantidad de ropa en esa talla
                    //         return response()->json(['success' => false, 'message' => 'No hay suficiente cantidad de ropa en la talla especificada'], 200);
                    //     }
                    // }

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

                    if ($talla == "M" || $talla === "L" || $talla === "S" || $talla === "XL" || $talla === "10" || $talla === "12" || $talla === "14") {
                        $ropas = DB::table("ROPA")
                            ->where('CODIGO', $codigoProducto)
                            ->first();
            
                        $nombreCampo = 'T_' . $talla;
            
                        // Obtener la cantidad actual de ropa para la talla específica
                        $cantidadActual = $ropas->{$nombreCampo};
            
                        // Verificar si hay suficiente cantidad de ropa
                        if ($cantidadActual > 0) {
                            // Calcular la nueva cantidad de ropa
                            $nuevaCantidadRopa = $cantidadActual - 1;
            
                            // Actualizar la cantidad de ropa en la tabla
                            $actualizarPrenda = DB::table("ROPA")
                                ->where('CODIGO', $codigoProducto)
                                ->update([
                                    $nombreCampo => $nuevaCantidadRopa,
                                ]);
                        } else {
                            // Manejar el caso donde no hay suficiente cantidad de ropa en esa talla
                            return response()->json(['success' => false, 'message' => 'No hay suficiente cantidad de ropa en la talla especificada'], 200);
                        }
                    }

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

        $venta= DB::TABLE("VENTA")->leftjoin("FACTURA as fac","fac.ID_FACTURA","=","VENTA.ID_FACTURA")->where('VENTA.ID_FACTURA',$idVenta)
        ->select("VENTA.TOTAL","fac.ID_TIPO_PAGO as ID_TIPO_PAGO")->get();



        $totalVenta=0;

        $tipoPago="";
        foreach($venta as $item){
            $totalVenta=$totalVenta+$item->TOTAL;
            $tipoPago=$item->ID_TIPO_PAGO;
        }

        if($tipoPago!="fiado"){

        $ventaActualizada= DB::TABLE("FACTURA")->where('ID_FACTURA',$idVenta)->update([
            'TOTAL'=>$totalVenta,
        ]);
    }else{
        return response()->json(['success' => false, 'message' => 'Se ha registrado venta como FIADO. Ve a la seccion Fiados cuando se ejecute el pago.'], 200);
    }
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

    public function getVentaFiada()
    {
        $facturas = DB::table("FACTURA")->where('FACTURA.ID_TIPO_PAGO', 'fiado')->get();
    
        foreach ($facturas as $factura) {
            $idFactura = $factura->ID_FACTURA;
            $ventas = DB::table("VENTA")->leftjoin("PRODUCTO as prod","prod.CODIGO","=","VENTA.CODIGO_PRODUCTO")->where('VENTA.ID_FACTURA', $idFactura)->select("VENTA.TOTAL as TOTAL","prod.NOMBRE as NOMBRE","VENTA.CANTIDAD")->get();
            $total = 0;
    
            foreach ($ventas as $venta) {
                $total += $venta->TOTAL;
            }
    
            $factura->VENTA = $ventas;
            $factura->TOTAL = $total;
        }
    
        if ($facturas->isNotEmpty()) {
            return response()->json(['success' => true, 'ventas' => $facturas], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontraron ventas'], 200);
        }
    }

        public function cobrarVentaFiada(Request $request){
                $idFactura=$request->idFactura;
                $total=$request->total;
                $tipoDePago=$request->tipoPago;

                $ventaActualizada= DB::TABLE("FACTURA")->where('ID_FACTURA',$idFactura)->update([
                    'TOTAL'=>$total,
                    'ID_TIPO_PAGO'=>$tipoDePago
                ]);

                if($ventaActualizada){

                    

                    return response()->json(['success' => true, 'message' => 'Venta cobrada'], 200);
                }else{
                    return response()->json(['success' => false, 'message' => 'No se pudo cobrar la venta'], 200);
                }

        }
    }
    
    

