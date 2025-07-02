<?php
// ResponesService.php: Clase del servicio para tareas de respuestas
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

class ResponseService 
{
    /**
	 * Prepara respuesta para el mensaje de error
     * 
     * @param string $mesnaje con el mensaje de error a devolver
     * @return array con los datos de la respuesta
	 */
    static function respuestaError($mensaje) 
    {
        return array(
            "status" => "error",
            "message" => $mensaje
        );
    }


    /**
	 * Prepara respuesta para el mensaje de éxito
     * 
     * @param string $mensaje con el mensaje a devolver
     * @param string $codigo con el código de respuesta HTTP
     * @param array $datos con los datos de la respuesta
     * @return array con los datos de la respuesta
	 */
    static function respuestaDatos($mensaje, $codigo, $datos) 
    {
        return array(
            "status" => "success",
            "message" => $mensaje,
            "data" => $datos
        );
    }
}