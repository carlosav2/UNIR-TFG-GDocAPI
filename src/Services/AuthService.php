<?php
// AuthService.php: Calse del servicio para tareas de autenticación
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

use GDocAPI\Services\RequestService;
use GDocAPI\Services\SecurityService;
use GDocAPI\Services\SiteService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService 
{
    /**
	 * Devulve los roles necesarios para realizar busquedas
     * 
     * @return array con respuesta con los roles
	 */
    static function obtenerRolesBusqueda()
    {
        $site = SiteService::getSiteUp();
        return ["ROLE_READER_$site", "ROLE_WRITER_$site", "ROLE_ADMIN_$site"];
    }


    /**
	 * Devulve los roles necesarios para buscar tags
     * 
     * @return array con respuesta con los roles
	 */
    static function obtenerRolesBusquedaTag() 
    {
        $site = SiteService::getSiteUp();
        return ["ROLE_TAG_R_$site", "ROLE_TAG_W_$site", "ROLE_ADMIN_$site"];
    }


    /**
	 * Devulve los roles necesarios para edición
     * 
     * @return array con respuesta con los roles
	 */
    static function obtenerRolesEdicion()
    {
        $site = SiteService::getSiteUp();
        return ["ROLE_WRITER_$site", "ROLE_ADMIN_$site"];
    }


    /**
	 * Devulve los roles necesarios para edición de tags
     * 
     * @return array con respuesta con los roles
	 */
    static function obtenerRolesEdicionTag() 
    {
        $site = SiteService::getSiteUp();
        return ["ROLE_TAG_W_$site", "ROLE_ADMIN_$site"];
    }


    /**
	 * Devulve los roles necesarios para acciones de admin
     * 
     * @return array con respuesta con los roles
	 */
    static function obtenerRolesAdmin()
    {
        $site = SiteService::getSiteUp();
        return ["ROLE_ADMIN_$site"];
    }


    /**
	 * Valida que el consumidor tenga los roles necesarios para realizar
     * la acción solicitada
     * 
     * @param array $roles con los roles necesarios
     * @param string $mensajeError Mensaje a devolver
     * @return void 
     * @throws Exception Si no tiene los roles necearios.
	 */
    static function validarRoles($roles, $mensajeError)
    {
        $rolesValidos = false;

        foreach ($roles as $rol)
        {
            // Si el consumidor tiene al menos uno de roles necesarios valida la acción
            if (in_array($rol, $_POST["ROLE"]))
            {
                $rolesValidos = true;
                break;
            }
        } 

        // En caso de que el consumidor no posea alguno de los roles necesarios se lanza una excepción personalizada
        if (!$rolesValidos)
            throw new \Exception($mensajeError, 401);
    }


    /**
	 * Obtiene un token si las credenciales vienen en el cuerpo
     * 
     * @return JWT con el token
     * @throws Exception Si falta usuario y/o contraseña
	 */
    static function obtenerToken()
    {
        $datos = RequestService::obtenerContenidoCuerpo();
        $usuario = $datos['usuario'] ?? null;
        $passwd = $datos['passwd'] ?? null;

        if ($usuario && $passwd)
        {
            $token = self::crearToken([$usuario, $passwd]);
            //Le asignamos a la variable el usuario para que posteriormente lo pueda emplear el auditor
            SecurityService::$globarUsuario = $usuario;
            return $token;
        }
        else
        {
            throw new \Exception("JSON no valido",400);
        }       
    }


    /**
	 * Genera el token si las credenciales son correctas
     * 
     * @param array $credenciales con el usuario y contraseña
     * @return array con el access token y el refresh token
     * @throws Exception Si se produce error al generar el JWT
     * @throws Exception Si las credenciales no son válidas
	 */
    static function crearToken($credenciales) 
    {
        $jwt = "";

        if (self::comprobarCredenciales($credenciales))
        {
            $payload = [
                'exp' => strtotime("now") + JWT_ACCESS_EXP, 
                'app' => $credenciales[0],
                'role' => $_POST["ROLE"],
                'type' => "access"
            ];

            $refreshPayload = [
                'exp' => strtotime("now") + JWT_REFRESH_EXP,
                'app' => $credenciales[0],
                'type' => "refresh"
            ];

            try 
            {
                $accessToken = JWT::encode($payload, SECRET_KEY, 'HS256');
                $refreshToken = JWT::encode($refreshPayload, SECRET_KEY, 'HS256');
            }
            catch (\Exception $e)
            {
                throw new \Exception("Error al generar el token", 401);
            }
        }
        else
        {
            throw new \Exception("Credenciales no validas", 401);
        }

        //return $jwt;
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }


    /**
	 * Comprueba que las credenciales sean correctas
     * 
     * @param array $credenciales  con el usuario y contraseña
     * @return bool true si las credenciales son correctas o no (false)
	 */
    static function comprobarCredenciales($credenciales)
    {
        foreach (USER_PASSWD as $credencial) 
        {
            if ($credencial[0] == $credenciales[0])
            {
                if ($credencial[1] == $credenciales[1])
                {
                    $_POST["ROLE"] = $credencial[2];
                    return true;
                }
            }
        }
        return false;
    }


     /**
	 * Obtiene los roles de una aplicación y los mete en $_POST["ROLE"]
     * 
     * @param array $aplicacion aplicación de la que obtener los roles
     * @return void 
	 */
    static function obtenerRoles($aplicacion)
    {
        foreach (USER_PASSWD as $credencial) 
        {
            if ($credencial[0] == $aplicacion)
            {
                $_POST["ROLE"] = $credencial[2];
                return;
            }
        }
    }


    /**
	 * Comprueba que el JWT sea válido
     * 
     * @param JWT $token con el JWT
     * @return bool true si el JWT es correcto
     * @throws Exception si el JWT ha expirado
     * @throws Exception si se produce un error al validar el JWT
	 */
    static function comprobarToken($token)
    {
        try {
            // Decodificar el token
            $decoded = JWT::decode($token, new Key(SECRET_KEY, 'HS256'));
  
            // Verifica si es un refresh token
            if ($decoded->type != "access")
            {
                throw new \Exception('No es un access token');
            }

            // Verificar si el token no ha expirado
            if ($decoded->exp < time()) {
                throw new Exception('El token ha expirado');
            }
  
            SecurityService::$globarUsuario = $decoded->app;

            // Si todo es correcto, el token es válido
            $_POST["ROLE"] = $decoded->role;
            return true;

        } 
        catch (\Exception $e) 
        {
            throw new \Exception("Error al validar token: ". $e->getMessage(), 401);
        }
    }


    /**
	 * Comprueba que metodo de autenticación está usando el cliente
     * 
     * @return string con la seguridad usada por el cliente. 
	 */
    static function seguridadUsada()
    {
        $seguridad = "NONE";

        if (isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            if (preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) 
            {
                $seguridad = "JWT";
            }
            if (preg_match('/Basic\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) 
            {
                $seguridad = "BASIC";
            }
        }

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
        {
            $seguridad = "BASIC_PHP";
        }

        return $seguridad;
    }


    /**
	 * Actualiza el JWT con el refresh token
     * 
     * @param JWT con el refresh token
     * @return JWT con el token actualizado
     * @throws Exception JSON no válido
     * @throws Exception No es un refresh token
     * @throws Exception El token ha expirado
     * @throws Exception Error al generar el token
     * @throws Exception Error al actualizar el token
	 */
    static function actualizarToken() 
    {
        $datos = RequestService::obtenerContenidoCuerpo();
        $token = $datos["refresh_token"] ?? throw new \Exception("JSON no válido", 400);
        
        try 
        {
            $decoded = JWT::decode($token, new Key(SECRET_KEY, 'HS256'));

            // Verifica si es un refresh token
            if ($decoded->type != "refresh")
            {
                throw new \Exception('No es un refresh token');
            }

            // Verificar si el token no ha expirado
            if ($decoded->exp < time()) 
            {
                throw new \Exception('El token ha expirado');
            }

            self::obtenerRoles($decoded->app);

            $payload = [
                'exp' => strtotime("now") + JWT_ACCESS_EXP, 
                'app' => $decoded->app,
                'role' => $_POST["ROLE"],
                'type' => "access"
            ];

            $refreshPayload = [
                'exp' => strtotime("now") + JWT_REFRESH_EXP,
                'app' => $decoded->app,
                'type' => "refresh"
            ];

            try 
            {
                $accessToken = JWT::encode($payload, SECRET_KEY, 'HS256');
                $refreshToken = JWT::encode($refreshPayload, SECRET_KEY, 'HS256');
                return [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken
                ];
            }
            catch (\Exception $e)
            {
                throw new \Exception("Error al generar el token", 401);
            }
        }
        catch (\Exception $e)
        {
            throw new \Exception("Error al actualizar el token: ". $e->getMessage(), 401);
        }
    }
        
}