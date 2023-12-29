<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Carbon;

class articulosController extends Controller
{
    
    public function guardarProducto(Request $request){
        $nombre = $request->nombre;
        $foto=$request->foto?:null;
        $descripcion=$request->descripcion;
        $categoria=$request->categoria;
        $subcategoria=$request->subcategoria;
        $costo=$request->costo;
        $precio=$request->precio;
        $cantidad=$request->cantidad;
        $personaje=$request->personaje?:null;
        $tallaS=$request->tallaS?:null;
        $tallaM=$request->tallaM?:null;
        $tallaL=$request->tallaL?:null;
        // $cantidadRopa=sum([$tallaS,$tallaM,$tallaL]);

     

        $fechaIngreso = Carbon::now();
        if($categoria!=="3"){
           
            
        $idProducto = DB::table("PRODUCTO")->insertGetId([
            'NOMBRE' => $nombre,
            'FOTO' => $foto,
            'DESCRIPCION' => $descripcion,
            'ID_CATEGORIA' => $categoria,
            'ID_SUBCATEGORIA' => $subcategoria,
            'COSTO' => $costo,
            'PRECIO' => $precio,
            'CANTIDAD' => $cantidad,
            'ID_PERSONAJE' => $personaje || null,
            // 'FECHA_INGRESO' => $fechaIngreso->toDateTimeString(),

        ]);
  
        $productoInsertado = DB::table("PRODUCTO")->where('ID_PRODUCTO', $idProducto)
        ->leftjoin("CATEGORIA as cat","cat.ID_CATEGORIA","=","PRODUCTO.ID_CATEGORIA")
        ->leftjoin("SUBCATEGORIA as subcat","subcat.ID","=","PRODUCTO.ID_SUBCATEGORIA")
        ->leftjoin("PERSONAJE as persona","persona.ID_PERSONAJE","=","PRODUCTO.ID_PERSONAJE")
        ->leftjoin("ANIME as anime","anime.ID_ANIME","=","persona.ID_ANIME")
        ->select("PRODUCTO.ID_PRODUCTO","anime.NOMBRE as NOMBRE_ANIME","persona.NOMBRE as NOMBRE_PERSONAJE","cat.NOMBRE as NOMBRE_CATEGORIA","subcat.NOMBRE as NOMBRE_SUBCAT","PRODUCTO.NOMBRE as NOMBRE_PRODUCTO","PRODUCTO.FOTO","PRODUCTO.DESCRIPCION","PRODUCTO.COSTO","PRODUCTO.PRECIO","PRODUCTO.CODIGO","PRODUCTO.CANTIDAD")
        ->first();

        $categoriaid=$productoInsertado->NOMBRE_CATEGORIA;
        $subcategoriaid=$productoInsertado->NOMBRE_SUBCAT;
       
        
        $categoriaid=substr($categoriaid,0,3);
        $subcategoriaid=substr($subcategoriaid,0,3);
        $codigoUnico= "";
        if($personaje!==null && $personaje!=="" && $personaje!=="null"){
            $personaje=substr($personaje,0,3);
            $codigoUnico=$categoriaid.'-'.$subcategoriaid.'-'.$personaje.'-'.$idProducto;
            $codigoUnico=strtoupper($codigoUnico);
        }else{
            $codigoUnico=$categoriaid.'-'.$subcategoriaid.'-'.$idProducto;
        }
  

        $insertarCodigo=db::table("PRODUCTO")->where('ID_PRODUCTO',$idProducto)->update(['CODIGO'=>$codigoUnico]);
    }elseif($categoria==="3"){
        $prendaInsertada=DB::table("ROPA")->insertGetId([
            'NOMBRE' => $nombre,
            'FOTO' => $foto,
            'DESCRIPCION' => $descripcion,
            'ID_SUBCATEGORIA' => $subcategoria,
            'COSTO' => $costo,
            'PRECIO' => $precio,
            'ID_PERSONAJE' => $personaje || null,
            'S' => $tallaS,
            'M' => $tallaM,
            'L' => $tallaL,

        ]);

        $productoInsertado = DB::table("ROPA")->where('ROPA.ID', $prendaInsertada)
        ->leftjoin("SUBCATEGORIA as subcat","subcat.ID","=","ROPA.ID_SUBCATEGORIA")
        ->select("ROPA.ID","subcat.NOMBRE as NOMBRE_SUBCAT","ROPA.NOMBRE as NOMBRE_PRODUCTO","ROPA.FOTO","ROPA.DESCRIPCION","ROPA.COSTO","ROPA.PRECIO","ROPA.S","ROPA.M","ROPA.L")
        ->first();

        $nombreProducto=$productoInsertado->NOMBRE_PRODUCTO;
        $subcategoriaid=$productoInsertado->NOMBRE_SUBCAT;

        $nombreProducto=substr($nombreProducto,0,3);
        $subcategoriaid=substr($subcategoriaid,0,3);
        $codigoUnico= "";

        if($personaje!==null && $personaje!=="" && $personaje!=="null"){
            $personaje=substr($personaje,0,3);
            $codigoUnico=$nombreProducto.'-'.$subcategoriaid.'-'.$personaje.'-'.$prendaInsertada;
            $codigoUnico=strtoupper($codigoUnico);
        }else{
            $codigoUnico=$nombreProducto.'-'.$subcategoriaid.'-'.$prendaInsertada;
            $codigoUnico=strtoupper($codigoUnico);

        }


        $insertarCodigo=db::table("ROPA")->where('ID',$prendaInsertada)->update(['CODIGO'=>$codigoUnico]);

        return response()->json(['mensaje' => 'Producto Ingresado con exito',$productoInsertado, $insertarCodigo], 200);
    }



        //generacion de codigo unico



        if($idProducto){
        return response()->json(['mensaje' => 'Producto Ingresado con exito',$categoria, $insertarCodigo], 200);
    }else{
        return response()->json(['mensaje' => 'Error al ingresar el producto'], 500);
    }

    }

    public function getArticulos(){ 
        $request = DB::table('PRODUCTO')
        ->leftjoin("CATEGORIA as cat","cat.ID_CATEGORIA","=","PRODUCTO.ID_CATEGORIA")
        ->leftjoin("SUBCATEGORIA as subcat","subcat.ID","=","PRODUCTO.ID_SUBCATEGORIA")
        ->leftjoin("PERSONAJE as persona","persona.ID_PERSONAJE","=","PRODUCTO.ID_PERSONAJE")
        ->leftjoin("ANIME as anime","anime.ID_ANIME","=","persona.ID_ANIME")
        ->select("PRODUCTO.ID_PRODUCTO","anime.NOMBRE as NOMBRE_ANIME","persona.NOMBRE as NOMBRE_PERSONAJE","cat.NOMBRE as NOMBRE_CATEGORIA","subcat.NOMBRE as NOMBRE_SUBCAT","PRODUCTO.NOMBRE as NOMBRE_PRODUCTO","PRODUCTO.FOTO","PRODUCTO.DESCRIPCION","PRODUCTO.COSTO","PRODUCTO.PRECIO","PRODUCTO.CODIGO","PRODUCTO.CANTIDAD")
        ->get();
        $ropa = DB::table('ROPA')
        ->leftjoin("SUBCATEGORIA as subcat", "subcat.ID", "=", "ROPA.ID_SUBCATEGORIA")
        ->select("ROPA.ID as ID_PRODUCTO", "ROPA.CODIGO as CODIGO", "subcat.NOMBRE as NOMBRE_SUBCAT", "ROPA.NOMBRE as NOMBRE_PRODUCTO", "ROPA.FOTO", "ROPA.DESCRIPCION", "ROPA.COSTO", "ROPA.PRECIO", "ROPA.S as TALLA_S", "ROPA.M as TALLA_M", "ROPA.L as TALLA_L")
        ->get();
    
    foreach ($ropa as $producto) {
        $cantidadTallaS = $producto->TALLA_S;
        $cantidadTallaM = $producto->TALLA_M;
        $cantidadTallaL = $producto->TALLA_L;
    
        $cantidadRopa = array_sum([$cantidadTallaS, $cantidadTallaM, $cantidadTallaL]);
    
        // Agregar la cantidad total al objeto producto
        $producto->CANTIDAD = $cantidadRopa;
    
        // Agregar las cantidades por talla al array TALLAS dentro del objeto producto
        $producto->TALLAS = [
            "S" => $cantidadTallaS,
            "M" => $cantidadTallaM,
            "L" => $cantidadTallaL
        ];


    }


    
        


        return response()->json(["productos"=>$request,"ropa"=>$ropa]);

      
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
