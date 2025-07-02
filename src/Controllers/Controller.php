<?php
// Controller.php: Clase abstracta padre de los Controllers
// Se encarga de ejecutar las funciones que le vengan como parametro
// y centraliza las respuestas de ok y error (captura de las excepciones)
// Carlos Ahumada Vidal

namespace GDocAPI\Controllers;

use GDocAPI\Services\ResponseService;
use GDocAPI\Services\AuditorService;

abstract class Controller 
{
    /**
	 * Ejecuta el método solicitado
	 * @param string $function método a ejecutar
     * @param int $http_code código de respuesta HTTP
     * @param string $parametros para el método
     * @return JSON con respuesta 
	 */
    static function execute($function, $http_code, ...$parametros)
    {
        try
        {
            $respuesta = response()->json(call_user_func_array([static::class, $function], $parametros), $http_code);
            //Llamamos al método del auditor para las ejecuciones exitosas
            AuditorService::auditar();
            return $respuesta;
        }
        catch (\Exception $e)
        {
            $respuesta = response()->json(ResponseService::respuestaError($e->getMessage()), $e->getCode());
            //Llamamos al método del auditor para las ejecuciones erroneas
            AuditorService::auditarError();
            return $respuesta;
        }
    }
}