<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
class usuarioController extends Controller
{
    //

    public function login(Request $request)
    {
        $usuario = $request->nickname;
        $password = $request->password;
    
        // Realiza una consulta a la base de datos para encontrar al usuario
        $user = DB::table('USUARIOS')->where('NICKNAME', $usuario)->first();


    
        if ($user) {
            // Verifica la contraseña
            if ( $password == $user->PASSWORD) {
                // Las credenciales son válidas, genera un token JWT
                $usuarioJWTSubject = new class ($user) implements JWTSubject {
                    private $usuarioRegistrado;
    
                    public function __construct($usuarioRegistrado)
                    {
                        $this->usuarioRegistrado = $usuarioRegistrado;
                    }
    
                    public function getJWTIdentifier()
                    {
                        return $this->usuarioRegistrado->ID_USUARIO;
                    }
    
                    public function getJWTCustomClaims()
                    {
                        return [  'nickname' => $this->usuarioRegistrado->NICKNAME,
                    
                        'rol' => $this->usuarioRegistrado->ID_PUESTO,'id'=>$this->usuarioRegistrado->ID_USUARIO];
                    }
                };
    
                $token = JWTAuth::fromUser($usuarioJWTSubject);

    
                return response()->json(['status' => 'success', 'token' => $token]);
            } else {
                // Las credenciales no son válidas
                return response()->json(['status' => 'error', 'message' => 'Contraseña incorrecta']);
            }
        } else {
            // El usuario no fue encontrado
            return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado']);
        }
    }
    
}
