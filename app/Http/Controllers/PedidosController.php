<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PedidosController extends Controller
{

    public function getPedidos()
    {
        $conteoEstados = ['TODO' => 0]; // Inicializamos TODO con 0
        $resultado = DB::table('COMPRAS')
            ->leftjoin("ESTADO", "ESTADO.ID_ESTADO", "=", "COMPRAS.ID_ESTADO")
            ->where('ES_INTERNACIONAL', 1)
            ->select("ESTADO.ESTADO")
            ->get();
    
        foreach ($resultado as $res) {
            $estado = $res->ESTADO;
    
            // Incrementar el conteo para el estado correspondiente
            if (array_key_exists($estado, $conteoEstados)) {
                $conteoEstados[$estado]++;
                $conteoEstados['TODO']++;
            } else {
                $conteoEstados[$estado] = 1;
                $conteoEstados['TODO']++;
            }
        }
        
        if ($resultado) {
            return response()->json(['success' => true, 'resultado' => $resultado, 'conteoEstados' => $conteoEstados], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontraron pedidos'], 200);
        }
    }
    
    public function getComprasPedidos(Request $request){
        $estado=$request->estado;


        
        switch($estado){
            case "1":
                $resultado= DB::TABLE("COMPRAS")->leftJoin("PROVEEDORES_INTERNACIONAL","COMPRAS.ID_PROVEEDORES_INTERNACIONAL","=","PROVEEDORES_INTERNACIONAL.ID")->leftJoin("ESTADO","COMPRAS.ID_ESTADO","=","ESTADO.ID_ESTADO")->select("COMPRAS.ID_COMPRA","COMPRAS.FECHA_COMPRA","COMPRAS.MONTO","COMPRAS.DESCRIPCION","COMPRAS.ID_ESTADO","COMPRAS.ID_PROVEEDORES_INTERNACIONAL","COMPRAS.FECHA_ESTIMADA_LLEGADA","PROVEEDORES_INTERNACIONAL.NOMBRE_PROVEEDOR_INTER","ESTADO.ESTADO")->where('COMPRAS.ID_ESTADO',1)->get();
                break;
            case "2":
                $resultado= DB::TABLE("COMPRAS")->leftJoin("PROVEEDORES_INTERNACIONAL","COMPRAS.ID_PROVEEDORES_INTERNACIONAL","=","PROVEEDORES_INTERNACIONAL.ID")->leftJoin("ESTADO","COMPRAS.ID_ESTADO","=","ESTADO.ID_ESTADO")->select("COMPRAS.ID_COMPRA","COMPRAS.FECHA_COMPRA","COMPRAS.MONTO","COMPRAS.DESCRIPCION","COMPRAS.ID_ESTADO","COMPRAS.ID_PROVEEDORES_INTERNACIONAL","COMPRAS.FECHA_ESTIMADA_LLEGADA","PROVEEDORES_INTERNACIONAL.NOMBRE_PROVEEDOR_INTER","ESTADO.ESTADO")->where('COMPRAS.ID_ESTADO',2)->get();
                break;
            case "3":
                $resultado= DB::TABLE("COMPRAS")->leftJoin("PROVEEDORES_INTERNACIONAL","COMPRAS.ID_PROVEEDORES_INTERNACIONAL","=","PROVEEDORES_INTERNACIONAL.ID")->leftJoin("ESTADO","COMPRAS.ID_ESTADO","=","ESTADO.ID_ESTADO")->select("COMPRAS.ID_COMPRA","COMPRAS.FECHA_COMPRA","COMPRAS.MONTO","COMPRAS.DESCRIPCION","COMPRAS.ID_ESTADO","COMPRAS.ID_PROVEEDORES_INTERNACIONAL","COMPRAS.FECHA_ESTIMADA_LLEGADA","PROVEEDORES_INTERNACIONAL.NOMBRE_PROVEEDOR_INTER","ESTADO.ESTADO")->where('COMPRAS.ID_ESTADO',3)->get();
                break;
                case "4":
                    $resultado = DB::table("COMPRAS")
                        ->leftJoin("PROVEEDORES_INTERNACIONAL", "COMPRAS.ID_PROVEEDORES_INTERNACIONAL", "=", "PROVEEDORES_INTERNACIONAL.ID")
                        ->leftJoin("ESTADO", "COMPRAS.ID_ESTADO", "=", "ESTADO.ID_ESTADO")
                        ->leftJoin("VALORACIONES", "VALORACIONES.ID_COMPRA", "=", "COMPRAS.ID_COMPRA")
                        ->select("COMPRAS.ID_COMPRA", "COMPRAS.FECHA_COMPRA", "COMPRAS.MONTO", "COMPRAS.DESCRIPCION", "COMPRAS.ID_ESTADO", "COMPRAS.ID_PROVEEDORES_INTERNACIONAL", "COMPRAS.FECHA_ESTIMADA_LLEGADA", "PROVEEDORES_INTERNACIONAL.NOMBRE_PROVEEDOR_INTER", "ESTADO.ESTADO")
                        ->where('COMPRAS.ID_ESTADO', 4)
                        ->get();
                
                    foreach ($resultado as $res) {
                        $id_compra = $res->ID_COMPRA;
                        $valoracion = DB::table("VALORACIONES")->where("ID_COMPRA", $id_compra)->first();
                
                        // Agrega una propiedad 'valoracion' al objeto $res
                        $res->valoracion = $valoracion ? true : false;
                    }
                    

                break;
            case "5":
                $resultado = DB::table("COMPRAS")
                ->leftJoin("PROVEEDORES_INTERNACIONAL", "COMPRAS.ID_PROVEEDORES_INTERNACIONAL", "=", "PROVEEDORES_INTERNACIONAL.ID")
                ->leftJoin("ESTADO", "COMPRAS.ID_ESTADO", "=", "ESTADO.ID_ESTADO")
                ->leftJoin("VALORACIONES", "VALORACIONES.ID_COMPRA", "=", "COMPRAS.ID_COMPRA")
                ->select("COMPRAS.ID_COMPRA", "COMPRAS.FECHA_COMPRA", "COMPRAS.MONTO", "COMPRAS.DESCRIPCION", "COMPRAS.ID_ESTADO", "COMPRAS.ID_PROVEEDORES_INTERNACIONAL", "COMPRAS.FECHA_ESTIMADA_LLEGADA", "PROVEEDORES_INTERNACIONAL.NOMBRE_PROVEEDOR_INTER", "ESTADO.ESTADO")
                ->where('COMPRAS.ES_INTERNACIONAL',1)
                ->where('COMPRAS.ID_ESTADO','!=',5)
                ->get();
        
            foreach ($resultado as $res) {
                $id_compra = $res->ID_COMPRA;
                $valoracion = DB::table("VALORACIONES")->where("ID_COMPRA", $id_compra)->first();
        
                // Agrega una propiedad 'valoracion' al objeto $res
                $res->valoracion = $valoracion ? true : false;
            }
            

                break;
            case "6":
                $resultado= DB::TABLE("COMPRAS")->leftJoin("PROVEEDORES_INTERNACIONAL","COMPRAS.ID_PROVEEDORES_INTERNACIONAL","=","PROVEEDORES_INTERNACIONAL.ID")->leftJoin("ESTADO","COMPRAS.ID_ESTADO","=","ESTADO.ID_ESTADO")->select("COMPRAS.ID_COMPRA","COMPRAS.FECHA_COMPRA","COMPRAS.MONTO","COMPRAS.DESCRIPCION","COMPRAS.ID_ESTADO","COMPRAS.ID_PROVEEDORES_INTERNACIONAL","COMPRAS.FECHA_ESTIMADA_LLEGADA","PROVEEDORES_INTERNACIONAL.NOMBRE_PROVEEDOR_INTER","ESTADO.ESTADO")->where('COMPRAS.ID_ESTADO',5)->get();
                $dias= 0;

                foreach ($resultado as $res) {
                    $fechaPedido = $resultado[0]->FECHA_COMPRA;
                    $fechaActual = date("Y-m-d");
                    $dias = (strtotime($fechaActual) - strtotime($fechaPedido)) / 86400;
                    
                    // Agregar la propiedad 'dias_retrasados' a cada elemento
                    $res->dias_retrasados = $dias;

                }

                break;
 

            
        }



        if($resultado){
            return response()->json(['success' => true, 'resultado' => $resultado], 200);

    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron pedidos'], 200);


    }



    }

    public function cambiarEstadoPedido(Request $request){
        $id_compra=$request->id;
        $id_estado=$request->estado;

        switch($id_estado){
            case "1":
                $resultado= DB::table('COMPRAS')->where('ID_COMPRA',$id_compra)->update(['ID_ESTADO' => 2]);
                $logs_compras=DB::table("LOGS_COMPRAS")->insert(["ID_COMPRA"=>$id_compra,"FECHA"=>date("Y-m-d"),"DESCRIPCION"=>'Se cambio el estado de la compra a '.'EN TRANSITO']);

                break;
            case "2":
                $resultado= DB::table('COMPRAS')->where('ID_COMPRA',$id_compra)->update(['ID_ESTADO' => 3]);
                $logs_compras=DB::table("LOGS_COMPRAS")->insert(["ID_COMPRA"=>$id_compra,"FECHA"=>date("Y-m-d"),"DESCRIPCION"=>'Se cambio el estado de la compra a '.'EN GUATEMALA']);

                break;
            case "3":
                $resultado= DB::table('COMPRAS')->where('ID_COMPRA',$id_compra)->update(['ID_ESTADO' => 4]);
                $logs_compras=DB::table("LOGS_COMPRAS")->insert(["ID_COMPRA"=>$id_compra,"FECHA"=>date("Y-m-d"),"DESCRIPCION"=>'Se cambio el estado de la compra a '.'OBTENIDO']);

                break;

        }


        if($resultado){
            return response()->json(['success' => true, 'resultado' => $resultado,'message' => 'Cambio de estado Exitoso'], 200);

    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron pedidos'], 200);

    }

}

public function retrasarEstadoPedido(Request $request){
    $id_compra=$request->id;
    $id_estado=$request->estado;

    switch($id_estado){

        case "2":
            $resultado= DB::table('COMPRAS')->where('ID_COMPRA',$id_compra)->update(['ID_ESTADO' => 1]);
            break;
        case "3":
            $resultado= DB::table('COMPRAS')->where('ID_COMPRA',$id_compra)->update(['ID_ESTADO' => 2]);
            break;
        case "4":
            $resultado= DB::table('COMPRAS')->where('ID_COMPRA',$id_compra)->update(['ID_ESTADO' => 3]);
            break;
        case "5":
            $resultado= DB::table('COMPRAS')->where('ID_COMPRA',$id_compra)->update(['ID_ESTADO' => 3]);
            break;

    }

    if($resultado){
        return response()->json(['success' => true, 'resultado' => $resultado], 200);

}else{
    return response()->json(['success' => false, 'message' => 'No se encontraron pedidos'], 200);

}
}
    
public function pedidoAtrasadoDeTiempo(Request $request){
    $id_compra=$request->id;



    $resultado= DB::table('COMPRAS')->where('ID_COMPRA',$id_compra)->update(['ID_ESTADO' => 5]);

    if($resultado){
        return response()->json(['success' => true, 'resultado' => $resultado], 200);
}
else{
    return response()->json(['success' => false, 'message' => 'No se encontraron pedidos'], 200);

}

}

public function getSatisfaccion(){
    $resultado= DB::table('SATISFACCION')->get();

    if($resultado){
        return response()->json(['success' => true, 'resultado' => $resultado], 200);
}
else{
    return response()->json(['success' => false, 'message' => 'No se encontraron pedidos'], 200);
}
}

public function valorarPedido(Request $request){
    $id_compra=$request->id;
    $valoracion=$request->valoracion;
    $comentario=$request->comentario;

    $resultado= DB::table('VALORACIONES')->insert(["ID_COMPRA"=>$id_compra,"ID_SATISFACCION"=>$valoracion,"OBSERVACIONES"=>$comentario]);

    if($resultado){
        return response()->json(['success' => true, 'resultado' => $resultado], 200);
}
else{
    return response()->json(['success' => false, 'message' => 'No se encontraron pedidos'], 200);
}
}
}
