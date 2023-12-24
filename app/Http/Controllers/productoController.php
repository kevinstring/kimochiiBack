<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class productoController extends Controller
{
    //
    public function getProductos(){
        $resultados = DB::table("PRODUCTO")->get();

        // Devolver una respuesta JSON
        return response()->json(["request" => $resultados]);
    }
}
