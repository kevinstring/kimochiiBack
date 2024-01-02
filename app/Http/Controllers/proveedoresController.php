<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class proveedoresController extends Controller
{
public function getProveedores(){
    $proveedores= DB::TABLE("PROVEEDORES")->get();

    if($proveedores){
        return response()->json(['success' => true, 'proveedores' => $proveedores], 200);

}else{
    return response()->json(['success' => false, 'message' => 'No se encontraron proveedores'], 200);

}}

public function postProveedores(Request $request){
    
    $nombreProveedor=$request->nombreProveedor;
    $nombreTienda=$request->nombreTienda;
    $telefono=$request->telefono;
    $direccion=$request->direccion;
    
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
        ->select("COMPRAS.*", "prove.NOMBRE_PROVEEDOR", "prove.NOMBRE_TIENDA")
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


}
