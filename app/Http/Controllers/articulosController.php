<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
class articulosController extends Controller
{
    

    public function getArticulos(){ 
        $request = DB::table('PRODUCTO')->get();

        return $request;
    }

    public function postCategoria(Request $request){
            $categoria= $request->categoria;

            $crearCategoria=DB::table('CATEGORIA')->insert(['NOMBRE'=>$categoria]);

            if($crearCategoria){
                return response()->json($categoria, 200);
            }
    }
    public function postSubCategoria(Request $request){
        $subcategoria= $request->subcategoria;
        $subcategorias= db::table('SUBCATEGORIA')->SELECT('NOMBRE')->get();

        foreach ($subcategorias as $sub) {
            if ($sub->NOMBRE === $subcategoria) {
                return response()->json(["error" => "Esta subcategoría ya existe"], 422);
            }
        }

        $crearSubCategoria=DB::table('SUBCATEGORIA')->insert(['NOMBRE'=>$subcategoria]);

        if($crearSubCategoria){
            return response()->json($subcategoria, 200);
        }
}

public function getCategorias(){
    $getCategoria=db::table("CATEGORIA")->get();
    $getSubCategoria = DB::table("SUBCATEGORIA")
    ->leftJoin("CATEGORIA as cat", "cat.ID_CATEGORIA", "=", "SUBCATEGORIA.ID_CATEGORIA")
    ->select("SUBCATEGORIA.*","cat.NOMBRE as NOMBRECAT")
    ->get();


    return response()->json(["categoria"=>$getCategoria,"subcategoria"=>$getSubCategoria]);
    
}

public function getSubCategorias(Request $request){
    $ref=$request->ref;

    $getSub=db::table("SUBCATEGORIA")->where('ID_CATEGORIA',$ref)->get();

    return response()->json(['subcategoria'=>$getSub]);
}

public function asignarSubCategoria(Request $request){
    $subcategoria = $request->subCategoria;
    $categoria=$request->categoria;
 
    $asignarSubCat=db::table('SUBCATEGORIA')->where('ID',$subcategoria)->update(["ID_CATEGORIA"=>$categoria]);

    if($asignarSubCat){
        return response()->json(["creada"=>$asignarSubCat],200);
    }else{
        return response()->json(["error" => "Esta subcategoría ya existe"], 422);
    }

}

}
