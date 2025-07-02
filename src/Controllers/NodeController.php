<?php
// NodeController.php: Clase controlador de las rutas nodeRouter
// Carlos Ahumada Vidal

namespace GDocAPI\Controllers;

use GDocAPI\Services\NodeService;
use GDocAPI\Services\ResponseService;
use GDocAPI\Services\RequestService;
use GDocAPI\Services\AuthService;

class NodeController extends Controller
{
    /**
	 * Obtiene toda la información de un nodo a a partir de su id
     * 
	 * @param string $nodoId id del nodo
     * @return json con respuesta 
	 */
    static function obtenerInfoNodo($idNodo) 
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusqueda(), "No tiene permisos para consultar nodos");

        $datos = NodeService::obtenerInfoNodo($idNodo);

        return ResponseService::respuestaDatos("Datos obtenidos con éxito", 200, $datos);
    }


    /**
	 * Busca un nodo por su nombre, se devuelve toda la información de ese nodo (id, etc.)
     * 
	 * @param string $nodoName nombre del nodo 
     * @return json con respuesta 
	 */
    static function buscarNodo($nodoName)
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusqueda(), "No tiene permisos para consultar nodos");

        $datos = NodeService::buscarNodo($nodoName);

        $array_nodos = array();

        foreach ($datos->list->entries as $key => $value) 
        {
            $array_nodos[$key] = $value->entry;
        }
        
        return ResponseService::respuestaDatos("Datos obtenidos con éxito", 200, $array_nodos);
    }


    /**
	 * Busca un nodo ENTIDAD por su nombre, se devuelve toda la información de ese nodo (id, etc.)
     * 
	 * @param string $nodoName nombre del nodo
     * @return json con respuesta 
	 */
    static function buscarNodoId($nodoName)
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusqueda(), "No tiene permisos para consultar nodos");

        $datos = NodeService::buscarNodo($nodoName, NODO_RAIZ_ID);

        $array_nodos = array();

        foreach ($datos->list->entries as $key => $value) 
        {
            $array_nodos[$key] = $value->entry;
        }
        
        return ResponseService::respuestaDatos("Datos obtenidos con éxito", 200, $array_nodos);
    }


    /**
	 * Busca si un nodo padre tiene un nodo hijo con cierto nombre, se devuelve toda la información de ese nodo (id, etc.)
     * 
	 * @param string $nodoName nombre del nodo
     * @param string $nodoPadre id del nodo padre
     * @return json con respuesta 
	 */
    static function buscarNodoHijo($nodoName, $nodoPadre)
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusqueda(), "No tiene permisos para consultar nodos");

        $datos = NodeService::buscarNodo($nodoName, $nodoPadre);

        $array_nodos = array();

        foreach ($datos->list->entries as $key => $value) 
        {
            $array_nodos[$key] = $value->entry;
        }
        
        return ResponseService::respuestaDatos("Datos obtenidos con éxito", 200, $array_nodos);
    }


    /**
	 * Obtiene los hijos de un nodo a partir de su id
     * 
	 * @param string $nodoId id del nodo
     * @return json con respuesta 
	 */
    static function obtenerHijosNodo($nodoId) 
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusqueda(), "No tiene permisos para consultar nodos");

        $datos = NodeService::obtenerHijosNodo($nodoId);

        return ResponseService::respuestaDatos("Datos obtenidos con éxito", 200, $datos);
    }


    /**
	 * Obtiene los hijos de un nodo a partir de su nombre
     * 
	 * @param string $nodoName nombre del nodo
     * @param string $nodoPadre id del nodo padre
     * @return json con respuesta 
	 */
    static function obtenerHijosNodoNombre($nodoPadre, $nodoName) 
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusqueda(), "No tiene permisos para consultar nodos");
        
        // Obtenemos el ID de la carpeta padre enviada
        $idPadre = NodeService::buscarNodo($nodoName, $nodoPadre)->list->entries[0]->entry->id ?? throw new \Exception("No existen nodos hijos" , 400);

        // Obtenemos los hijos
        $hijos = NodeService::obtenerHijosNodo($idPadre);

        return ResponseService::respuestaDatos("Datos obtenidos con éxito", 200, $hijos);
    }
    

    /**
	 * Crea un nodo
     * 
	 * @param string $nombre nombre del nodo
     * @param string $nodoPadre id del nodo padre (opcional)
     * @param string $tipo tipo de nodo (opcional)
     * @return json con respuesta 
	 */
    static function crearNodo($nodoPadre, $nombre = null, $tipo = null)
    {
        AuthService::validarRoles(AuthService::obtenerRolesEdicion(), "No tiene permisos para crear nodos");
        
        if (!$nombre && !$tipo)
        {
            $datosNodo = RequestService::obtenerContenidoCuerpo();
            $nombre = trim($datosNodo["nombre"]) ?? null;
            $tipo = $datosNodo["tipo"] ?? null;
        }

        $datos = NodeService::crearNodo($nombre, $nodoPadre, $tipo);

        //Se asigna la variable global con el ID del nuevo nodo
        $GLOBALS["datosAauditor"] = $datos->id;

        return ResponseService::respuestaDatos("Nodo creado con éxito", 200, (array)$datos);
    }
}