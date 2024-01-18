<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class articulosController extends Controller
{
    
    public function guardarProducto(Request $request){
        $nombre = $request->nombre;
        $foto=$request->foto?:null;
        $descripcion=$request->descripcion;
        $tags=$request->tags;
        $categoria=$request->categoria;

        $costo=$request->costo;
        $precio=$request->precio;
        $cantidad=$request->cantidad;
        $personaje=$request->personaje;
        $tallaS=$request->tallaS?:null;
        $tallaM=$request->tallaM?:null;
        $tallaL=$request->tallaL?:null;
        $esRopa=$request->esRopa;
        $proveedor=$request->proveedor;

        // $cantidadRopa=sum([$tallaS,$tallaM,$tallaL]);

    
     
        $porcentajeGanancia=0;

        $fechaIngreso = Carbon::now();
        if($categoria){

           $porcentajeGanancia=($precio-$costo)/$costo*100;
            
        $idProducto = DB::table("PRODUCTO")->insertGetId([
            'NOMBRE' => $nombre,
            'FOTO' => $foto,
            'DESCRIPCION' => $descripcion,
            'ID_TAG' => $tags,
            'ID_CATEGORIA' => $categoria,
            'COSTO' => $costo,
            'PRECIO' => $precio,
            'CANTIDAD' => $cantidad,
            'ID_PERSONAJE' => $personaje,
            'PORCENTAJE_GANANCIA' => $porcentajeGanancia,
            'ID_PROVEEDOR' => $proveedor || null,
            
            // 'FECHA_INGRESO' => $fechaIngreso->toDateTimeString(),

        ]);
  
        $productoInsertado = DB::table("PRODUCTO")->where('ID_PRODUCTO', $idProducto)
        ->leftjoin("TAGS as cat","cat.ID_TAG","=","PRODUCTO.ID_TAG")
        ->leftjoin("CATEGORIA as subcat","subcat.ID","=","PRODUCTO.ID_CATEGORIA")
        ->leftjoin("PERSONAJE as persona","persona.ID_PERSONAJE","=","PRODUCTO.ID_PERSONAJE")
        ->leftjoin("ANIME as anime","anime.ID_ANIME","=","persona.ID_ANIME")
        ->select("PRODUCTO.ID_PRODUCTO","anime.NOMBRE as NOMBRE_ANIME","persona.NOMBRE as NOMBRE_PERSONAJE","cat.NOMBRE as NOMBRE_TAG","subcat.NOMBRE as NOMBRE_SUBCAT","PRODUCTO.NOMBRE as NOMBRE_PRODUCTO","PRODUCTO.FOTO","PRODUCTO.DESCRIPCION","PRODUCTO.COSTO","PRODUCTO.PRECIO","PRODUCTO.CODIGO","PRODUCTO.CANTIDAD")
        ->first();

        $categoriaid=$productoInsertado->NOMBRE_PRODUCTO;
        $subcategoriaid=$productoInsertado->NOMBRE_SUBCAT;
       
        $pattern = '/\b\w+\b/';
        preg_match_all($pattern, $categoriaid, $palabras1);

$tresLetras1 = "";
        if (isset($palabras1[0]) && count($palabras1[0]) >= 2) {
            // Obtener la segunda palabra y las tres letras
            $segundaPalabra1 = $palabras1[0][1];
            $tresLetras1 = Str::substr($segundaPalabra1, 0, 3);
            echo $tresLetras1 . "\n";
        }

        $categoriaid=substr($categoriaid,0,3);
        $subcategoriaid=substr($subcategoriaid,0,3);
        $codigoUnico= "";
        if($personaje!==null && $personaje!=="" && $personaje!=="null"){
            $personaje=substr($personaje,0,3);
            $codigoUnico=$tresLetras1.'-'.$subcategoriaid.'-'.$personaje.'-'.$idProducto;
            $codigoUnico=strtoupper($codigoUnico);
        }else{
            $codigoUnico=$tresLetras1.'-'.$subcategoriaid.'-'.$idProducto;
        }
  

        $insertarCodigo=db::table("PRODUCTO")->where('ID_PRODUCTO',$idProducto)->update(['CODIGO'=>$codigoUnico]);
    }   
    if($esRopa==="true"){
        $prendaInsertada=DB::table("ROPA")->insertGetId([
            'NOMBRE' => $nombre,
            'FOTO' => $foto,
            'DESCRIPCION' => $descripcion,
            'ID_SUBCATEGORIA' => $subcategoriaid || null,
            'COSTO' => $costo,
            'PRECIO' => $precio,
            'ID_PERSONAJE' => $personaje || null,
            'S' => $tallaS,
            'M' => $tallaM,
            'L' => $tallaL,

        ]);

        $productoInsertado = DB::table("ROPA")->where('ROPA.ID', $prendaInsertada)
        ->leftjoin("CATEGORIA as subcat","subcat.ID","=","ROPA.ID_CATEGORIA")
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

        return response()->json(['mensaje' => 'Producto Ingresado con exitoo',$productoInsertado, $insertarCodigo], 200);
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
        ->leftjoin("TAGS as cat","cat.ID_TAG","=","PRODUCTO.ID_TAG")
        ->leftjoin("CATEGORIA as subcat","subcat.ID","=","PRODUCTO.ID_CATEGORIA")
        ->leftjoin("PERSONAJE as persona","persona.ID_PERSONAJE","=","PRODUCTO.ID_PERSONAJE")
        ->leftjoin("ANIME as anime","anime.ID_ANIME","=","persona.ID_ANIME")
        ->select("PRODUCTO.ID_PRODUCTO","anime.NOMBRE as NOMBRE_ANIME","persona.NOMBRE as NOMBRE_PERSONAJE","cat.NOMBRE as NOMBRE_TAG","subcat.NOMBRE as NOMBRE_SUBCAT","PRODUCTO.NOMBRE as NOMBRE_PRODUCTO","PRODUCTO.FOTO","PRODUCTO.DESCRIPCION","PRODUCTO.COSTO","PRODUCTO.PRECIO","PRODUCTO.CODIGO","PRODUCTO.CANTIDAD","PRODUCTO.PORCENTAJE_GANANCIA")
        ->get();
        $ropa = DB::table('ROPA')
        ->leftjoin("CATEGORIA as subcat", "subcat.ID", "=", "ROPA.ID_SUBCATEGORIA")
        ->select("ROPA.ID as ID_PRODUCTO", "ROPA.CODIGO as CODIGO", "subcat.NOMBRE as NOMBRE_SUBCAT", "ROPA.NOMBRE as NOMBRE_PRODUCTO", "ROPA.FOTO", "ROPA.DESCRIPCION", "ROPA.COSTO", "ROPA.PRECIO", "ROPA.S as TALLA_S", "ROPA.M as TALLA_M", "ROPA.L as TALLA_L")
        ->get();
    
        $favorito=DB::table('FAVORITOS')->get();
        $sumaTotalDeCantidad=0;

        foreach($request as $cantidad){
            $sumaTotalDeCantidad+=$cantidad->CANTIDAD;
        }

       foreach($request as $prod){
            $idFavorito=$prod->CODIGO;
            // if (is_string($prod->FOTO)) {
            //     $prod->FOTO = str_replace("https://kimochii.s3.amazonaws.com", '', $prod->FOTO);
            // }
            $prod->FOTO = json_decode($prod->FOTO);
            if (is_array($prod->FOTO)) {
                $prod->FOTO = array_map(function ($url) {
                    return [
                        'image' => $url,
                        'thumbImage' => $url, // Puedes ajustar esto según tus necesidades
                        'alt' => 'alt of image',
                        'title' => 'title of image'
                    ];
                }, $prod->FOTO);
            } else {
                // Si FOTO no es un array, puedes manejarlo de acuerdo a tus necesidades
                $prod->FOTO = [];
            }

            //convertir $prod->FOTO a un arreglo aunque sea de un solo elemento 
          
            

            foreach($ropa as $producto){
                if($producto->CODIGO===$idFavorito){
                    $producto->FAVORITO=true;
                }
            }
        }
        foreach($request as $prod){
            $idFavorito=$prod->CODIGO;
            foreach($request as $producto){
                if($producto->CODIGO===$idFavorito){
                    $producto->FAVORITO=true;
                }
            }
        }
    

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


    
        


        return response()->json(["productos"=>$request,"ropa"=>$ropa,"total"=>$sumaTotalDeCantidad]);

      
    }

    public function postCategoria(Request $request){
            $categoria= $request->categoria;

            $categorias= db::table('CATEGORIA')->SELECT('NOMBRE')->get();

            foreach ($categorias as $cat) {
                if ($cat->NOMBRE === $categoria) {
                    return response()->json(["error" => "Esta categoría ya existe"], 422);
                }
            }

            $crearCategoria=DB::table('CATEGORIA')->insert(['NOMBRE'=>$categoria]);



            if($crearCategoria){
                return response()->json($categoria, 200);
            }
    }
    
    public function postTag(Request $request){
        $subcategoria= $request->subcategoria;
        $subcategorias= db::table('TAGS')->SELECT('NOMBRE')->get();

        foreach ($subcategorias as $sub) {
            if ($sub->NOMBRE === $subcategoria) {
                return response()->json(["error" => "Esta subcategoría ya existe"], 422);
            }
        }

        $crearSubCategoria=DB::table('TAGS')->insert(['NOMBRE'=>$subcategoria]);

        if($crearSubCategoria){
            return response()->json($subcategoria, 200);
        }
}

public function getCategorias(){
    $getTags=db::table("TAGS")->get();
    $getAnimes=db::table("ANIME")->get();
    $getPersonajes=db::table("PERSONAJE")->get();

    $getCategoria=db::table("CATEGORIA")->get();



    return response()->json(["categoria"=>$getCategoria,"tags"=>$getTags,"animes"=>$getAnimes,"personajes"=>$getPersonajes]);
    
}
public function postAnime(Request $request){
    $anime= $request->nombre;

    $animes= db::table('ANIME')->SELECT('NOMBRE')->get();

    foreach ($animes as $ani) {
        if ($ani->NOMBRE === $anime) {
            return response()->json(["error" => "Este anime ya existe"], 422);
        }
    }

    $crearAnime=DB::table('ANIME')->insert(['NOMBRE'=>$anime]);

    if($crearAnime){
        return response()->json($anime, 200);
    }
}

public function postPersonaje(Request $request){
    $personaje= $request->nombre;
    $anime=$request->anime;

    $personajes= db::table('PERSONAJE')->SELECT('NOMBRE')->get();

    foreach ($personajes as $per) {
        if ($per->NOMBRE === $personaje) {
            return response()->json(["error" => "Este personaje ya existe"], 422);
        }
    }

    $crearPersonaje=DB::table('PERSONAJE')->insert(['NOMBRE'=>$personaje,'ID_ANIME'=>$anime]);

    if($crearPersonaje){
        return response()->json($personaje, 200);
    }
}

public function getSubCategorias(Request $request){
    $ref=$request->ref;

    $getSub=db::table("CATEGORIA")->where('ID',$ref)->get();

    return response()->json(['subcategoria'=>$getSub]);
}

public function asignarSubCategoria(Request $request){
    $subcategoria = $request->subCategoria;
    $categoria=$request->categoria;
 
    $asignarSubCat=db::table('CATEGORIA')->where('ID',$subcategoria)->update(["ID_TAG"=>$categoria]);

    if($asignarSubCat){
        return response()->json(["creada"=>$asignarSubCat],200);
    }else{
        return response()->json(["error" => "Esta subcategoría ya existe"], 422);
    }

}

public function postFavorito(Request $request){
    $idProducto=$request->id;
  
    $favorito=db::table('FAVORITOS')->insert(['CODIGO_PRODUCTO'=>$idProducto]);

    if($favorito){
        return response()->json(["message"=>"Agregado a favoritos"],200);
    }else{
        return response()->json(["error" => "Error al agregar"], 422);
    }
}

public function getRopa(){
    $ropa = DB::table('ROPA')
    ->leftjoin("CATEGORIA as subcat", "subcat.ID", "=", "ROPA.ID_SUBCATEGORIA")
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

    return response()->json(["ropa"=>$ropa]);

}
}
