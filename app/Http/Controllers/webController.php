<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class webController extends Controller
{
    public function getProveedoresInter(){
        $proveedoresInternaciona= DB::TABLE("PROVEEDORES")->get();
   

        if($proveedoresInternaciona){
            return response()->json(['success' => true, 'proveedores'=>$proveedoresInternaciona], 200);
    }
    else{
        return response()->json(['success' => false, 'message' => 'No se encontraron proveedores'], 200);
    }
}

    public function getCategoriasW(){


        $getAnime = DB::table("ANIME")->select("NOMBRE")->get();

        foreach($getAnime as $anime){
            $anime->NOMBRE=ucwords(strtolower($anime->NOMBRE));
        }

        $categorias=DB::table("CATEGORIA")->where('OCULTO',0)->get();

        if($categorias){
            return response()->json(['success' => true, 'categorias'=>$categorias,'anime'=>$getAnime], 200);

        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron categorias'], 200);

        }

    }

    public function buscarPorUnAnime(Request $request){
        $anime=$request->anime;
        $pagina = $request->pagina ?? 1; // Página por defecto si no se proporciona
        $limit = 10;
        $offset = ($pagina - 1) * $limit;

        $getProductos = DB::table("PRODUCTO")
        ->leftJoin("PERSONAJE as person", "person.ID_PERSONAJE", "=", "PRODUCTO.ID_PERSONAJE")
        ->leftJoin("ANIME as an", "an.ID_ANIME", "=", "person.ID_ANIME")
        ->where('an.NOMBRE',$anime)
        ->where('PRODUCTO.OCULTO',0 )
        
        ->select("PRODUCTO.ID_PRODUCTO", "PRODUCTO.NOMBRE", "PRODUCTO.PRECIO", "PRODUCTO.DESCRIPCION", "PRODUCTO.FOTO", "person.NOMBRE as personaje", "an.NOMBRE as anime","PRODUCTO.CANTIDAD as CANTIDAD")
        ->limit($limit)
        ->offset($offset)
        ->get();

        foreach ($getProductos as $prod) {
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

                if($prod->CANTIDAD==0){
                    $prod->STOCK="AGOTADO";
            }else{
                $prod->STOCK="EN STOCK";
            }
            } else {
                $prod->FOTO = [];
            }
        }

        $cantidadDeProductos= DB::table("PRODUCTO")
        ->leftJoin("PERSONAJE as person", "person.ID_PERSONAJE", "=", "PRODUCTO.ID_PERSONAJE")
        ->leftJoin("ANIME as an", "an.ID_ANIME", "=", "person.ID_ANIME")
        ->where('an.NOMBRE',$anime)
        ->count();

        $cantidadDePaginas=ceil($cantidadDeProductos/$limit);

        if($getProductos){
            return response()->json(['success' => true, 'productos'=>$getProductos,'cantidadPaginas'=>$cantidadDePaginas], 200);

        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron categorias'], 200);

        }


    }

    public function getOneCategory(Request $request){
        $nombre = $request->nombre;
        $pagina = $request->pagina ?? 1; // Página por defecto si no se proporciona
        $limit = 10;
        $offset = ($pagina - 1) * $limit;


    
        $categoria = ($nombre !== "all") ? DB::table("CATEGORIA")->where('NOMBRE', $nombre)->select("ID")->first() : null;
        



        if ($nombre !== "all") {

            $cantidadDeProductos=0;

            $getProductos = DB::table("PRODUCTO")
                ->where('ID_CATEGORIA', $categoria->ID)
                ->leftJoin("PERSONAJE as person", "person.ID_PERSONAJE", "=", "PRODUCTO.ID_PERSONAJE")

                ->leftJoin("ANIME as an", "an.ID_ANIME", "=", "person.ID_ANIME")
                ->select("PRODUCTO.ID_PRODUCTO", "PRODUCTO.NOMBRE", "PRODUCTO.PRECIO", "PRODUCTO.DESCRIPCION", "PRODUCTO.FOTO", "person.NOMBRE as personaje", "an.NOMBRE as anime","PRODUCTO.CANTIDAD as CANTIDAD","PRODUCTO.CODIGO as CODIGO")
                ->where('PRODUCTO.OCULTO',0 )
                ->limit($limit)
                ->offset($offset)
                ->get();

              $cantidadDeProductos= DB::table("PRODUCTO")
                ->where('ID_CATEGORIA', $categoria->ID)
                             ->count();

                             $cantidadDePaginas=ceil($cantidadDeProductos/$limit);
        } else {
            $getProductos = DB::table("PRODUCTO")
                ->leftJoin("PERSONAJE as person", "person.ID_PERSONAJE", "=", "PRODUCTO.ID_PERSONAJE")
                ->leftJoin("ANIME as an", "an.ID_ANIME", "=", "person.ID_ANIME")
                ->where('PRODUCTO.OCULTO',0 )
                ->limit($limit)
                ->offset($offset)
                ->select("PRODUCTO.ID_PRODUCTO", "PRODUCTO.NOMBRE", "PRODUCTO.PRECIO", "PRODUCTO.DESCRIPCION", "PRODUCTO.FOTO", "person.NOMBRE as personaje", "an.NOMBRE as anime","PRODUCTO.CANTIDAD as CANTIDAD")
        
                
                ->get();

                $cantidadDeProductos= DB::table("PRODUCTO")
                ->count();

                $cantidadDePaginas=ceil($cantidadDeProductos/$limit);
        }
    
        foreach ($getProductos as $prod) {
            $prod->FOTO = json_decode($prod->FOTO);
            if (is_array($prod->FOTO)) {
                $prod->FOTO = array_map(function ($url) {
                    return [
                        'path' => $url,
                        'thumbImage' => $url, // Puedes ajustar esto según tus necesidades
                        'alt' => 'alt of image',
                        'title' => 'title of image'
                    ];
                }, $prod->FOTO);
            } else {
                $prod->FOTO = [];
            }

            if($prod->CANTIDAD==0){
                    $prod->STOCK="AGOTADO";
            }else{
                $prod->STOCK="EN STOCK";

            }


 
        }

    
        if ($getProductos->count() > 0) {
            return response()->json(['success' => true, 'productos' => $getProductos,'cantidadPaginas'=>$cantidadDePaginas], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontraron categorías'], 200);
        }
    }
    

    public function buscar(Request $request){
        $nombre = $request->busqueda;
        $pagina = $request->pagina ?? 1; // Página por defecto si no se proporciona
        $limit = 10;
        $offset = ($pagina - 1) * $limit;
    
        $getProductos = DB::table("PRODUCTO")
            ->where(function($query) use ($nombre) {
                $query->orWhere('PRODUCTO.NOMBRE', 'like', '%' . $nombre . '%')
                      ->orWhere('PRODUCTO.DESCRIPCION', 'like', '%' . $nombre . '%')
                      ->orWhere('ANIME.NOMBRE', 'like', '%' . $nombre . '%')
                      ->orWhere('CATEGORIA.NOMBRE', 'like', '%' . $nombre . '%')
                      ->orWhere('PERSONAJE.NOMBRE', 'like', '%' . $nombre . '%');
            })
            ->where('PRODUCTO.OCULTO',0)
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
                "CATEGORIA.NOMBRE as categoria",
                "PRODUCTO.CANTIDAD as CANTIDAD",
                "PRODUCTO.CODIGO as CODIGO"
            )
            ->offset($offset)
            ->limit($limit)
            ->get();
    
        foreach ($getProductos as $prod) {
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
                $prod->FOTO = [];
            }

            if($prod->CANTIDAD==0){
                $prod->STOCK="AGOTADO";
        }else{
            $prod->STOCK="EN STOCK";
        }
        }

            
    
      $productosCount=  DB::table("PRODUCTO")
      ->where(function($query) use ($nombre) {
          $query->orWhere('PRODUCTO.NOMBRE', 'like', '%' . $nombre . '%')
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


            ->count();
    
     $cantidadDePaginas=ceil($productosCount/$limit);
        if ($getProductos->count() > 0) {
            return response()->json(['success' => true, 'productos' => $getProductos,'cantidadPaginas'=>$cantidadDePaginas], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontraron productos'], 200);
        }
    }
    

    public function getMasVendidos(){
        $ventas = DB::table("VENTA")->select("CODIGO_PRODUCTO", DB::raw("COUNT(CODIGO_PRODUCTO) as cantidad",))
            ->groupBy("CODIGO_PRODUCTO")->orderBy("cantidad", "DESC")->limit(8)->get();
    
        $productos = [];
    
        foreach($ventas as $venta){
            // Intenta obtener el producto correspondiente
            $producto = DB::table("PRODUCTO")->where("CODIGO", $venta->CODIGO_PRODUCTO)->where("OCULTO",0)->first();
           
          
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
    $producto=DB::table("PRODUCTO")
    ->leftjoin("COLABORADORES as col","col.ID","=","PRODUCTO.ID_COLABORADOR")
    ->where('PRODUCTO.NOMBRE',$id)->select("PRODUCTO.NOMBRE", "PRODUCTO.DESCRIPCION","PRODUCTO.PRECIO","PRODUCTO.FOTO","PRODUCTO.ID_TAG","PRODUCTO.CANTIDAD as CANTIDAD","PRODUCTO.ID_CATEGORIA as CATEGORIA","PRODUCTO.CODIGO as CODIGO","col.LINK","col.NOMBRE as NOMBRE_COLA")->first();
   
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

        if($producto->CANTIDAD==0){
            $producto->STOCK="AGOTADO";
    }else{
        $producto->STOCK="EN STOCK";
    }



    } else {
        // Si FOTO no es un array, puedes manejarlo de acuerdo a tus necesidades
        $producto->FOTO = [];

    }

      if($producto->CATEGORIA==22){
        $prenda= DB::table("ROPA")->where('CODIGO',$producto->CODIGO)->select("ROPA.T_S as TALLA_S","ROPA.T_M as TALLA_M","ROPA.T_L as TALLA_L","ROPA.T_XL as TALLA_XL","ROPA.T_10 as TALLA_10","ROPA.T_12 as TALLA_12","ROPA.T_14 as TALLA_14")->get();

        foreach($prenda as $prend){
            $cantidadTallaS = $prend->TALLA_S ?? 0;
            $cantidadTallaM = $prend->TALLA_M ?? 0;
            $cantidadTallaL = $prend->TALLA_L   ?? 0;
                $cantidadTallaXL = $prend->TALLA_XL ?? 0;
                $cantidadTalla10 = $prend->TALLA_10 ?? 0;
                $cantidadTalla12 = $prend->TALLA_12 ?? 0;
                $cantidadTalla14 = $prend->TALLA_14 ?? 0;
        }

        $producto->TALLAS = [
            "S" => $cantidadTallaS,
            "M" => $cantidadTallaM,
            "L" => $cantidadTallaL,
            "XL" => $cantidadTallaXL,
            "10" => $cantidadTalla10,
            "12" => $cantidadTalla12,
            "14" => $cantidadTalla14,

        ];

        foreach($producto->TALLAS as $key=>$talla){
            if($talla==0){
                unset($producto->TALLAS[$key]);

                   
        


            }

        }
        $tallasArray = [];
        foreach ($producto->TALLAS as $talla => $cantidad) {
            $tallasArray[] = ['talla' => $talla, 'cantidad' => $cantidad];
        }
    
        // Asignar el array de objetos al objeto $producto
        $producto->TALLAS = $tallasArray;

    
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
 
    // return $arrayTags;

     

$getTagss = DB::table("PRODUCTO")->select("ID_TAG")->get();

// Decodificar cada ID_TAG dentro de la colección
$getTags=[];
foreach ($getTagss as $producto) {
    $producto->ID_TAG = json_decode($producto->ID_TAG);
    array_push($getTags,$producto->ID_TAG);
}

// Filtrar los productos que contengan al menos dos de los tags

$filtrados = array_filter($getTags, function ($tags) use ($arrayTags) {
    // Verifica si $tags es un array antes de usar la función array_intersect
    if (is_array($tags)) {
        // Verifica si la intersección entre $tags y $arrayTags tiene al menos dos elementos
        return count(array_intersect($tags, $arrayTags)) >=5 || count(array_intersect($tags, $arrayTags)) >=1;
    } else {
        // Si $tags no es un array, puedes manejarlo de acuerdo a tus necesidades
        return false;
    }
});

// Obtener los productos que coincidan con los tags filtrados
$productos = DB::table("PRODUCTO")
    ->where(function ($query) use ($filtrados) {
        foreach ($filtrados as $tags) {
            $query->orWhereJsonContains("ID_TAG", $tags);
        }
    })
    // Agrega esta línea para obtener resultados aleatorios
    ->limit(5) // Agrega esta línea para obtener solo 5 resultados
    ->inRandomOrder()
    ->get();






    foreach($productos as $prod){
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
                
                ];
            }, $prod->FOTO);
        } else {
            // Si FOTO no es un array, puedes manejarlo de acuerdo a tus necesidades
            $prod->FOTO = [];
        }
    
    }

    
    if($productos){
        return response()->json(['success' => true, 'productos'=>$productos], 200);

    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron categorias'], 200);

    }


}

public function pasarelaRopa(){
    $getRopa=DB::table("PRODUCTO")->where("PRODUCTO.ID_CATEGORIA",22)->inRandomOrder()->take(5)->get();

    foreach($getRopa as $ropa){
        $ropa->FOTO = json_decode($ropa->FOTO);
        if (is_array($ropa->FOTO)) {
            $ropa->FOTO = array_map(function ($url) {
                return [
                    'image' => $url,
                    'thumbImage' => $url, // Puedes ajustar esto según tus necesidades
                    'alt' => 'alt of image',
                    'title' => 'title of image'
                ];
            }, $ropa->FOTO);
        } else {
            // Si FOTO no es un array, puedes manejarlo de acuerdo a tus necesidades
            $ropa->FOTO = [];
        }
    }

    if($getRopa){
        return response()->json(['success' => true, 'ropa'=>$getRopa], 200);

    }else{
        return response()->json(['success' => false, 'message' => 'No se encontraron categorias'], 200);

    }


}
}





