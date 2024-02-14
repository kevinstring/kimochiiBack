<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class gastosController extends Controller
{
    //

    public function postGasto(Request $request){
        $titulo = $request->titulo;
        $motivo = $request->motivo;
        $monto = $request->monto;
        $fecha = $request->fecha;

        $gastos = db::table('GASTOS')->insert([
            'TITULO' => $titulo,
            'MOTIVO' => $motivo,
            'MONTO' => $monto,
            'FECHA' => $fecha
        ]);

        if($gastos){
            return response()->json(['success' => true, 'message' => 'Gasto registrado'], 200);
        }else{
            return response()->json(['success' => false, 'message' => 'No se pudo registrar el gasto'], 200);
        }



    }

    public function getGastoYVentaTotal (){
        $gastos = db::table('GASTOS')->sum('MONTO');
        $ventas = db::table('FACTURA')->sum('TOTAL');

        $diferencia = $ventas - $gastos;

        $arreglito=[];

        $mensaje = "Las ventas superan a los gastos";
        $verdeORojo =0;

        if($diferencia<$ventas){
            $mensaje = "Las ventas superan a los gastos";

            $verdeORojo = 1;


             
        }else{
            $mensaje = "Los gastos superan a las ventas";
            $verdeORojo = 0;

        }


        if($gastos || $ventas){
            return response()->json(['success' => true, 'gastos' => $gastos, 'ventas' => $ventas,'mensaje'=>$mensaje,'turner'=>$verdeORojo], 200);
        }else{
            return response()->json(['success' => false, 'message' => 'No se encontraron gastos o ventas'], 200);
        }
    }



        public function getGastosPorTiempo(Request $request){
            $tipoTiempo=$request->tipoTiempo;


            
            $gastos =0;
            $ventas=0;
            $suma=0;
            $verdeORojo=0;
            $diferencia=0;



            $datos=[
                "gastos"=>$gastos,
                "ventas"=>$ventas,
                "diferencia"=>$diferencia,
                "verdeORojo"=>$verdeORojo
            ];
             
            if($tipoTiempo=="semana"){
                $gastos=DB::table("GASTOS")->whereBetween('FECHA', [date('Y-m-d', strtotime('-1 week')),  date('Y-m-d')]);
                $suma=$gastos->sum('MONTO');
                $ventas = db::table('FACTURA')->whereBetween('FECHA', [date('Y-m-d', strtotime('-1 week')),  date('Y-m-d')])->sum('TOTAL');

                if($ventas>$suma){
                    
                    $datos["mensaje"] = "Las ventas superan a los gastos";
                    $datos["verdeORojo"] = 1;
                    $datos["gastos"]=$suma;
                    $datos["ventas"]=$ventas;
                    $datos["diferencia"]=$ventas-$suma;
                }else{
                    $datos["mensaje"] = "Los gastos superan a las ventas";
                    $datos["verdeORojo"] = 0;
                    $datos["gastos"]=$suma;
                    $datos["ventas"]=$ventas;
                    $datos["diferencia"]=$ventas-$suma;
                }

                $gastos=$gastos->get();
                
            }else if($tipoTiempo=="mes"){
                $gastos=DB::table("GASTOS")->whereBetween('FECHA', [date('Y-m-d', strtotime('-1 month')),  date('Y-m-d')]);
                $suma=$gastos->sum('MONTO');
               
                $ventas = DB::table('FACTURA')
                ->whereBetween('FECHA', [now()->startOfMonth(), now()->endOfDay()])
                ->sum('TOTAL');                $diferencia=$ventas-$suma;
                if($ventas>$suma){
                    $datos["mensaje"] = "Las ventas superan a los gastos";
                    $datos["verdeORojo"] = 1;
                    $datos["gastos"]=$suma;
                    $datos["ventas"]=$ventas;
                    $datos["diferencia"]=$diferencia;
                    
                }else{
                    $datos["mensaje"] = "Los gastos superan a las ventas";
                    $datos["verdeORojo"] = 0;
                    $datos["gastos"]=$suma;
                    $datos["ventas"]=$ventas;
                    $datos["diferencia"]=$diferencia;
                }



                $gastos=$gastos->get();
            


            }else if($tipoTiempo=="dia"){
                $gastos=DB::table("GASTOS")->where('FECHA',[date('Y-m-d', strtotime('-1 day')),  date('Y-m-d')]);
                $suma=$gastos->sum('MONTO');

                $ventas = db::table('FACTURA')->where('FECHA',[date('Y-m-d', strtotime('-1 day')),  date('Y-m-d')])->sum('TOTAL');

                $diferencia=$ventas-$suma;
                if($ventas>$suma){
                    $datos["mensaje"] = "Las ventas superan a los gastos";
                    $datos["verdeORojo"] = 1;   
                    $datos["gastos"]=$suma;
                    $datos["ventas"]=$ventas;
                    $datos["diferencia"]=$diferencia;

                }else{
                    $datos["mensaje"] = "Los gastos superan a las ventas";
                    $datos["verdeORojo"] = 0;
                    $datos["gastos"]=$suma;
                    $datos["ventas"]=$ventas;
                    $datos["diferencia"]=$diferencia;
                }


                $gastos=$gastos->get();
               

                
            }


            if($gastos){
                return response()->json(['success' => true, 'gastos' => $gastos,'datos'=>$datos], 200);
            }else{
                return response()->json(['success' => false, 'message' => 'No se encontraron gastos'], 200);
            }
            



        }


}
