<?php
// CacheService.php: Clase del servicio para gestionar cache de nodos de la API
// Resuelve el problema de rstraso de indexación de nuevos nodo-folder
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

use Predis\Client;

class CacheService
{
    /**
	 * Guarda un nodo en cache
     * 
     * @param string $nodoNombre key a guardar
     * @param string $nodoId value a guardar
     * @return void
	 */
    static function setCache($nodoNombre, $nodoId)
    {
        $redis = new Client();
        $redis->setex($nodoNombre, REDIS_TIEMPO_CACHE, $nodoId);
    }


    /**
	 * Recupera un nodo de cache
     * 
     * @param string $nodoNombre key a recuperar
     * @return string con el id del nodo
	 */
    static function getCache($nodoNombre)
    {
        $redis = new Client();
        $nodoId = $redis->get($nodoNombre);
        return $nodoId;
    }
}