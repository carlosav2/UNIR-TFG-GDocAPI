<?php
// AuthController.php: Clase controlador de las rutas authRouter
// Carlos Ahumada Vidal

namespace GDocAPI\Controllers;

use GDocAPI\Services\ResponseService;
use GDocAPI\Services\AuthService;

class AuthController extends Controller
{
    /**
	 * Obtiene token
     * @return token 
	 */
    static function obtenerToken() 
    {
        $datos = AuthService::obtenerToken();
        return ResponseService::respuestaDatos("Token obtenido correctamente", 200, $datos);
    }  
    

    /**
	 * Renueva el token con el refresh token
     * @return token 
	 */
    static function renovarToken() 
    {
        $datos = AuthService::actualizarToken();
        return ResponseService::respuestaDatos("Token renovado correctamente", 200, $datos);
    }
}