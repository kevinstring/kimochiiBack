<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class proveedoresController extends Controller
{
public function getProveedores(){
    $proveedores= DB::TABLE("PROVEEDORES")->get();
    $proveedoresInternaciona= DB::TABLE("PROVEEDORES_INTERNACIONAL")->get();
    $estado= DB::TABLE("ESTADO")->get();

    if($proveedores){
        return response()->json(['success' => true, 'proveedores' => $proveedores,'proveedoresInter'=>$proveedoresInternaciona,'estado'=>$estado], 200);

}else{
    return response()->json(['success' => false, 'message' => 'No se encontraron proveedores'], 200);

}}

public function postProveedores(Request $request){
    
    $nombreProveedor=$request->nombreProveedor;
    $nombreTienda=$request->nombreTienda;
    $telefono=$request->telefono;
    $direccion=$request->direccion;
    $tipo=$request->tipo;

    

    if($tipo==='1'){
        $proveedores= DB::TABLE("PROVEEDORES")->insert([
            'NOMBRE_PROVEEDOR'=>$nombreProveedor,
            'NOMBRE_TIENDA'=>$nombreTienda,
            'TELEFONO'=>$telefono,
            'DIRECCION'=>$direccion,
        ]);


    if($proveedores){
        return response()->json(['success' => true, 'proveedores' => $proveedores,'message'=>"Proveedor agregado"], 200);
    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron proveedores'], 200);
    }
}
    else{
        $proveedores= DB::TABLE("PROVEEDORES_INTERNACIONAL")->insert([
            'NOMBRE_PROVEEDOR_INTER'=>$nombreProveedor,
            'REFERENCIA_WEB'=>$nombreTienda,
        ]);
    
        if($proveedores){
            return response()->json(['success' => true, 'proveedores' => $proveedores,'message'=>"Proveedor agregado"], 200);
        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron proveedores'], 200);
        }

    }

}

public function deleteProveedores(Request $request){
    $idProveedor=$request->idProveedor;
    $proveedores= DB::TABLE("PROVEEDORES")->where('ID_PROVEEDOR',$idProveedor)->delete();

    if($proveedores){
        return response()->json(['success' => true, 'proveedores' => $proveedores,'message'=>"Proveedor eliminado"], 200);
    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron proveedores'], 200);
    }


}

public function getCompras(Request $request)
{
    $indiceMes = $request->indiceMes;

    $compras = DB::table("COMPRAS")
        ->leftjoin("PROVEEDORES as prove", "prove.ID_PROVEEDOR", "=", "COMPRAS.ID_PROVEEDOR")
        ->leftjoin("PROVEEDORES_INTERNACIONAL as proveInter", "proveInter.ID", "=", "COMPRAS.ID_PROVEEDORES_INTERNACIONAL")
        ->select("COMPRAS.*", "prove.NOMBRE_PROVEEDOR", "prove.NOMBRE_TIENDA", "proveInter.NOMBRE_PROVEEDOR_INTER", "proveInter.REFERENCIA_WEB")
        ->whereRaw("MONTH(FECHA_COMPRA) = ?", [$indiceMes])
        ->get();

    $monto = 0;

    foreach ($compras as $compra) {
        $monto += $compra->MONTO;
    }

    $compras->MONTO_TOTAL = $monto;

    if ($compras) {
        return response()->json(['success' => true, 'compras' => $compras, 'granTotal' => $monto], 200);
    } else {
        return response()->json(['success' => false, 'message' => 'No se encontraron compras'], 200);
    }
}


public function postCompras(Request $request){
    $idProveedor=$request->idProveedor;
    $descripcion=$request->descripcion;
    $cantidad=$request->cantidad;
    $monto=$request->monto;
    $fecha=$request->fecha;
    $tipo=$request->tipo;
    $estado=$request->estado;
    $fechaInter=$request->fechaInter;
    $fechaLlegada=$request->fechaLlegada;
    $idProveedorInter=$request->idProveedorInter;


    if($tipo==='1'){
        $compras= DB::TABLE("COMPRAS")->insert([
            'ID_PROVEEDOR'=>$idProveedor,
            'DESCRIPCION'=>$descripcion,
            'MONTO'=>$monto,
            'FECHA_COMPRA'=>$fecha,
        ]);

        if($compras){
            return response()->json(['success' => true, 'compras' => $compras,'message'=>"Compra agregada"], 200);
        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron compras'], 200);
        }

    }
    else{
        $compras= DB::TABLE("COMPRAS")->insert([
            'ID_PROVEEDORES_INTERNACIONAL'=>$idProveedorInter,
            'DESCRIPCION'=>$descripcion,
            'MONTO'=>$monto,
            'FECHA_COMPRA'=>$fechaInter,
            'FECHA_ESTIMADA_LLEGADA'=>$fechaLlegada,
            'ID_ESTADO'=>$estado,
            'ES_INTERNACIONAL'=>1,
        ]);

        if($compras){
            return response()->json(['success' => true, 'compras' => $compras,'message'=>"Compra agregada"], 200);
        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron compras'], 200);
        }

    }


}

 
public function idTiendaDevolucion(Request $request){

    $idTienda=$request->id;

    $compras= DB::TABLE("COMPRAS")->where('ES_INTERNACIONAL',1)->where('ID_PROVEEDORES_INTERNACIONAL',$idTienda)->get();

    if($compras){
        return response()->json(['success' => true, 'compras' => $compras,'message'=>"Compra agregada"], 200);
    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron compras'], 200);
    }


}

public function ingresarDevolucion(Request $request){
    $id_proveedor=$request->id_proveedor;
    $id_compra=$request->id_compra;
    $descripcion=$request->descripcion;
 
    $monto=$request->monto;

    $fecha= date("Y-m-d");

    $devolucion= DB::TABLE("DEVOLUCIONES")->insert([
        'ID_TIENDA'=>$id_proveedor,
        'ID_COMPRA'=>$id_compra,
        'DESCRIPCION'=>$descripcion,
        'FECHA'=>$fecha,
        'MONTO'=>$monto,
    ]);

    if($devolucion){
    

        $compras= DB::TABLE("COMPRAS")->where('ID_COMPRA',$id_compra)->get();

        $montoCompra=$compras[0]->MONTO;

        $montoCompra=$montoCompra-$monto;

        $compras= DB::TABLE("COMPRAS")->where('ID_COMPRA',$id_compra)->update([
            'MONTO'=>$montoCompra,
        ]);

        if($compras){
            return response()->json(['success' => true, 'compras' => $compras,'message'=>"Devolucion agregada"], 200);
        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron compras'], 200);
        }
            


    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron devoluciones'], 200);
    }

}


}
