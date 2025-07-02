<?php
// RequestService.php: Clase del servicio para tareas de paramtros del request
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

class RequestService 
{
    /**
	 * Obtiene el cuerpo de la peticion HTTP
     * 
     * @return array con los datos del cuerpo
     * @throws Exception JSON no valido
	 */
    static function obtenerContenidoCuerpo()
    {
        $jsonBody = file_get_contents('php://input');
        $datos = json_decode($jsonBody, true);
        if ($datos === null && json_last_error() !== JSON_ERROR_NONE) 
        {
            throw new \Exception("JSON no válido", 400);
        }
        return $datos;
    }
}