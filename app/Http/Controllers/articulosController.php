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

}
