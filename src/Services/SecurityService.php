<?php
// SecurityService.php: Clase del servicio para tareas de seguridad
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

use GDocAPI\Services\AuthService;

class SecurityService
{
    // Variable que almacenara el usuario para ser empleado por el auditor
    static $globarUsuario;


    /**
	 * CORS: Comprueba origenes permitidos
     * 
     * @return void
     * @throws Exception Origen no permitido
     */
    static function validaCORS()
    {
        // Para el preflight request
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, Site');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        // Las rutas de documentación son totalmente públicas
        if (in_array($_SERVER["REQUEST_URI"], RUTAS_DOCUMENTACION))
        {
            return;
        }
        
        //header('Access-Control-Allow-Headers: Content-Type, Authorization, Site');
        
        $request_headers        = apache_request_headers();
        $http_origin            = $request_headers['Origin'] ?? null;
        $allowed_http_origins   = ORIGENES_PERMITIDOS;
        if ($http_origin)
        {
            if ((in_array($http_origin, $allowed_http_origins)))
            {  
                @header("Access-Control-Allow-Origin: " . $http_origin);
            }
            else
            {
                throw new \Exception("Origen no permitido", 401);
            }
        }
        else
        {
            throw new \Exception("Origen no permitido", 401);
        }
    }


    /**
	 * Valida la autenticación
     * 
     * @return void
     * @throws Exception Token no válido
     * @throws Exception Credenciales no validas
     * @throws Exception No se ha proporcionado token
     * @throws Exception No tiene permiso para acceder ha la ruta solicitada
     */
    static function validaToken()
    {
        // Realiza una comprobación de seguridad segúa el mecanismo que use el cliente
        switch (AuthService::seguridadUsada())
        {
            case "BASIC":
                if (! preg_match('/Basic\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) 
                {
                    throw new \Exception("Token no válido", 401);
                }
                $basic = $matches[1];
                $usuario = explode(":", $basic, 2)[0];
                $paswd = explode(":", $basic, 2)[1];
                $credenciales = [$usuario, $paswd];
                if (!AuthService::comprobarCredenciales($credenciales))
                {
                    throw new \Exception("Credenciales no validas", 401);
                }
                //Le asignamos a la variable el usuario
                self::$globarUsuario = explode(":", $basic, 2)[0];
                break;

            case "BASIC_PHP":
                $credenciales = [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']];
                if (!AuthService::comprobarCredenciales($credenciales))
                {
                    throw new \Exception("Credenciales no validas", 401);
                }
                self::$globarUsuario = $_SERVER['PHP_AUTH_USER'];
                break;

            case "JWT":
                if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) 
                {
                    throw new \Exception("Token no válido", 401);
                }
                $jwt = $matches[1];
                if (! $jwt) 
                {
                    throw new \Exception("No se ha proporcionado token", 401);
                }
                if (!AuthService::comprobarToken($jwt))
                {
                    throw new \Exception("Token no válido",401);
                }
                break;
                
            case "NONE":
                $uri = $_SERVER["REQUEST_URI"];
                if (!in_array($uri, RUTAS_PUBLICAS))
                {
                    throw new \Exception("No tiene permiso para acceder ha la ruta solicitada.", 401);
                }
                break;
            default:
                die;
                break;
        }
    }
}