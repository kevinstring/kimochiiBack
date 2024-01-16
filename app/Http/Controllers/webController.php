<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class webController extends Controller
{
    public function getProveedoresInter(){
        $proveedoresInternaciona= DB::TABLE("PROVEEDORES_INTERNACIONAL")->get();
   

        if($proveedoresInternaciona){
            return response()->json(['success' => true, 'proveedores'=>$proveedoresInternaciona], 200);
    }
    else{
        return response()->json(['success' => false, 'message' => 'No se encontraron proveedores'], 200);
    }
}

    public function getCategoriasW(){
        $categorias=DB::table("CATEGORIA")->where('OCULTO',0)->get();

        if($categorias){
            return response()->json(['success' => true, 'categorias'=>$categorias], 200);

        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron categorias'], 200);

        }

    }

    public function getOneCategory(Request $request){
        $nombre=$request->nombre;
        $categoria=DB::table("CATEGORIA")->where('NOMBRE',$nombre)->select("ID")->first();

        if($nombre!=="all"){
        $getProductos=DB::table("PRODUCTO")->where('ID_CATEGORIA',$categoria->ID)->leftjoin("PERSONAJE as person","person.ID_PERSONAJE","=","PRODUCTO.ID_PERSONAJE")
        ->leftjoin("ANIME as an","an.ID_ANIME","=","person.ID_ANIME")->select("PRODUCTO.ID_PRODUCTO","PRODUCTO.NOMBRE","PRODUCTO.PRECIO","PRODUCTO.DESCRIPCION","PRODUCTO.FOTO","person.NOMBRE as personaje","an.NOMBRE as anime")->get();
    }
    else{
        $getProductos=DB::table("PRODUCTO")->leftjoin("PERSONAJE as person","person.ID_PERSONAJE","=","PRODUCTO.ID_PERSONAJE")
        ->leftjoin("ANIME as an","an.ID_ANIME","=","person.ID_ANIME")->select("PRODUCTO.ID_PRODUCTO","PRODUCTO.NOMBRE","PRODUCTO.PRECIO","PRODUCTO.DESCRIPCION","PRODUCTO.FOTO","person.NOMBRE as personaje","an.NOMBRE as anime")->get();
   
        
    }
        foreach($getProductos as $prod){
            // $idFavorito=$prod->CODIGO;
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
        }

        if($getProductos){
            return response()->json(['success' => true, 'productos'=>$getProductos], 200);

        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron categorias'], 200);

        }
    }

    public function buscar(Request $request){
        $nombre = $request->busqueda;
    
        $getProductos = DB::table("PRODUCTO")
            ->where(function($query) use ($nombre) {
                $query->where('PRODUCTO.NOMBRE', 'like', '%' . $nombre . '%')
                      ->orWhere('PRODUCTO.DESCRIPCION', 'like', '%' . $nombre . '%')
                      ->orWhere('ANIME.NOMBRE', 'like', '%' . $nombre . '%')
                      ->orWhere('CATEGORIA.NOMBRE', 'like', '%' . $nombre . '%')
                      ->orWhere('PERSONAJE.NOMBRE', 'like', '%' . $nombre . '%');
                      
            })
            ->leftJoin("PERSONAJE", "PERSONAJE.ID_PERSONAJE", "=", "PRODUCTO.ID_PERSONAJE")
            ->leftJoin("ANIME", "ANIME.ID_ANIME", "=", "PERSONAJE.ID_ANIME")
            ->leftJoin("CATEGORIA", "CATEGORIA.ID", "=", "PRODUCTO.ID_CATEGORIA")
            ->select(
                "PRODUCTO.ID_PRODUCTO",
                "PRODUCTO.NOMBRE",
                "PRODUCTO.PRECIO",
                "PRODUCTO.DESCRIPCION",
                "PRODUCTO.FOTO",
                "PERSONAJE.NOMBRE as personaje",
                "ANIME.NOMBRE as anime",
                "CATEGORIA.NOMBRE as categoria"
            )
            ->get();
            foreach($getProductos as $prod){
                // $idFavorito=$prod->CODIGO;
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
            }
    
        if ($getProductos->count() > 0) {
            return response()->json(['success' => true, 'productos' => $getProductos], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontraron productos'], 200);
        }
    }

    public function getMasVendidos(){
        $ventas = DB::table("VENTA")->select("CODIGO_PRODUCTO", DB::raw("COUNT(CODIGO_PRODUCTO) as cantidad",))
            ->groupBy("CODIGO_PRODUCTO")->orderBy("cantidad", "DESC")->limit(5)->get();
    
        $productos = [];
    
        foreach($ventas as $venta){
            // Intenta obtener el producto correspondiente
            $producto = DB::table("PRODUCTO")->where("CODIGO", $venta->CODIGO_PRODUCTO)->first();
           
          
            // Verifica si $producto es nulo antes de acceder a sus propiedades
            if ($producto) {
      $producto = $this->procesarFotos($producto);
                // Accede a la propiedad 'cantidad' en lugar de 'CANTIDAD'
                $producto->CANTIDAD = $venta->cantidad;
    
                // Utiliza la función procesarFotos para procesar las imágenes
             
    
                array_push($productos, $producto);
            }
     
        }
    
        if(!empty($productos)){
            return response()->json(['success' => true, 'productos' => $productos], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontraron productos'], 200);
        }
    }
    
    // Agrega este método al controlador
private function procesarFotos($productos) {

      
    $productos->FOTO = json_decode($productos->FOTO);

        if (is_array($productos->FOTO)) {
            $productos->FOTO = array_map(function ($url) {
                return [
                    'image' => $url,
                    'thumbImage' => $url, // Puedes ajustar esto según tus necesidades
                    'alt' => 'alt of image',
                    'title' => 'title of image'
                ];
            }, $productos->FOTO);
        } else {
            // Si FOTO no es un array, puedes manejarlo de acuerdo a tus necesidades
            $productos->FOTO = [];
        }
 

    return $productos;
}

// Luego, en tu función existente, puedes llamar a este método
public function tuOtraFuncion() {
    // ...

    // Llama al método para procesar las fotos
  

    // Continúa con el resto de tu lógica

    // ...
}

public function getDetalleProducto(Request $request){
    $id=$request->id;
    $producto=DB::table("PRODUCTO")->where('NOMBRE',$id)->select("PRODUCTO.NOMBRE", "PRODUCTO.DESCRIPCION","PRODUCTO.PRECIO","PRODUCTO.FOTO","PRODUCTO.ID_TAG")->first();
    $producto->FOTO = json_decode($producto->FOTO);
    if (is_array($producto->FOTO)) {
        $producto->FOTO = array_map(function ($url) {
            return [
                'image' => $url,
                'thumbImage' => $url, // Puedes ajustar esto según tus necesidades
                'alt' => 'alt of image',
                'title' => 'title of image'
            ];
        }, $producto->FOTO);
    } else {
        // Si FOTO no es un array, puedes manejarlo de acuerdo a tus necesidades
        $producto->FOTO = [];
    }
    if($producto){
        return response()->json(['success' => true, 'producto'=>$producto], 200);

    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron categorias'], 200);

    }
}

public function getRecomendados(Request $request){

    $arrayTags = json_decode($request->tags, true);

    //obtener solo dos valores de array
    $arrayTags = array_slice($arrayTags, 0, 2);

     

    $getProducto= DB::table("PRODUCTO")
    ->leftJoin("PERSONAJE", "PERSONAJE.ID_PERSONAJE", "=", "PRODUCTO.ID_PERSONAJE")
    ->leftJoin("ANIME", "ANIME.ID_ANIME", "=", "PERSONAJE.ID_ANIME")
    ->leftJoin("CATEGORIA", "CATEGORIA.ID", "=", "PRODUCTO.ID_CATEGORIA")
    ->select(
        "PRODUCTO.ID_PRODUCTO",
        "PRODUCTO.NOMBRE",
        "PRODUCTO.PRECIO",
        "PRODUCTO.DESCRIPCION",
        "PRODUCTO.FOTO",
        "PRODUCTO.ID_TAG",
        "PERSONAJE.NOMBRE as personaje",
        "ANIME.NOMBRE as anime",
        "CATEGORIA.NOMBRE as categoria"
    )
    ->where(function($query) use ($arrayTags) {
        foreach ($arrayTags as $tag) {
            $query->orWhere('PRODUCTO.ID_TAG', 'like', '%' . $tag . '%');
        }
    })
    
    ->get();


    foreach($getProducto as $prod){
        // $idFavorito=$prod->CODIGO;
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
    
    }

    
    if($getProducto){
        return response()->json(['success' => true, 'productos'=>$getProducto], 200);

    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron categorias'], 200);

    }


}
}





