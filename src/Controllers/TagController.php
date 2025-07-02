<?php
// TagController.php: Clase controlador de las rutas tagRouter
// Carlos Ahumada Vidal

namespace GDocAPI\Controllers;

use GDocAPI\Controllers\Controller;
use GDocAPI\Services\AlfrescoService;
use GDocAPI\Services\ResponseService;
use GDocAPI\Services\RequestService;
use GDocAPI\Services\TagService;
use GDocAPI\Services\AuthService;

use GDocAPI\Services\FileService;

class TagController extends Controller 
{
    /**
	 * Obtiene todos los tags de un site
     * 
	 * @param bool $return_json si devuelve json (true), sino devuelve array (false)
     * @return json/array con respuesta 
	 */
    static function obtenerTodosTags($return_json = true) 
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusquedaTag(), "No tiene permisos para consultar tags");

        $url = URL_RUTAS . "tags";

        $datos = AlfrescoService::get($url);
        $entries = $datos->list->entries;
        $tags_array = array();

        foreach ($entries as $key => $value) 
        {
            $tags_array[$key] = $value->entry;
        }

        if ($return_json)
        {
            return ResponseService::respuestaDatos("Tags obtenidos con éxito", 200, $tags_array);
        }
        else
        {
            return $tags_array;
        }
    }
    
    
    /**
	 * Obtiene los tags de un nodo
     * 
     * @param string $nodoId id del nodo
	 * @param bool $return_json si devuelve json (true), sino devuelve array (false)
     * @return json/array con respuesta 
	 */
    static function obtenerTagsNodo($nodoId, $return_json = true)
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusquedaTag(), "No tiene permisos para consultar tags");

        $url = URL_RUTAS . "nodes/" . $nodoId . "/tags";
        $datos = AlfrescoService::get($url);

        if (isset($datos->error))
        {
            throw new \Exception("Error: " . $datos->error->briefSummary, 400);
        }
        $entries = $datos->list->entries;
        $tags_array = array();
        foreach ($entries as $key => $value) 
        {
            $tags_array[$key] = $value->entry;
        }
        if ($return_json)
        {
            return ResponseService::respuestaDatos("Tags obtenidos con éxito", 200, $tags_array);
        }
        else
        {
            return $tags_array;
        }
    }
    

    /**
	 * Asigna un tag a un nodo
     * 
     * @param string $nodoId id del nodo
     * @param bool $forzar si fuerza la creacion del tag si no existe
	 * @param bool $interno si devuelve json (false), sino devuelve array (true)
     * @return json/array con respuesta 
	 */
    static function asignarTagNodo($nodoId, $forzar = false, $interno = false) 
    {
        AuthService::validarRoles(AuthService::obtenerRolesEdicionTag(), "No tiene permisos para asignar tags");
        
        // Solo permite forzar (=crear) tags a usuarios con el ROLE_ADMIN
        if ($forzar)
        {
            AuthService::validarRoles(AuthService::obtenerRolesAdmin(), "No tiene permisos para forzar tags");
        }
        
        $url = URL_RUTAS . "nodes/$nodoId/tags";

        $tagsArray = array();

        // Obtenemos los tags
        $tags = $interno ? json_decode($_POST["tag"]) : RequestService::obtenerContenidoCuerpo();
        if ($interno) 
        {
            $tags = json_decode(json_encode($tags), true);
        }
        
        foreach ($tags as $key => $tag)
        {
            if ($key !== "tag")
            {
                throw new \Exception("JSON no válido", 400);
            }
            // Procesamos los tags para que tenga el formator que queremos
            $tag = trim(strtolower($tag));

            if (!TagService::existeTag($tag) && !$forzar)
            {
                throw new \Exception("El tag no existe. Solo se pueden asignar tags ya existentes.", 400); 
            }

            $tagsArray[] = array("tag" => $tag);
        }
        
        $datos = AlfrescoService::post($url, $tagsArray);

        isset($datos->error) ? throw new \Exception($datos->error->briefSummary, 400) : null;

        if(count($tagsArray) > 1)
        {
            // Si se han añadido varios tags se concatenan en el auditor todas las IDs
            $GLOBALS["datosAauditor"] = "";

            foreach ($datos->list->entries as $infoTag)
                $GLOBALS["datosAauditor"] .= $infoTag->entry->id . " | ";
        }
        else
        {
            // Si solo se ha añadido un tag se almacena el auditor el ID de ese tag
            $GLOBALS["datosAauditor"] = $datos->entry->id;
        }


        return ResponseService::respuestaDatos("Tag asignado", 200, $datos);
    }

    
    /**
	 * Modifica el nombre de un tag
     * 
     * @param string $tagId id del tag
     * @return array con respuesta 
     * @throws Exception JSON no válido
     * @throws Exception Error de Alfresco
	 */
    static function modificarTag($tagId) 
    {
        //Se comprueba que le consumidor de la API tenga los permisos necesarios
        AuthService::validarRoles(AuthService::obtenerRolesEdicionTag(), "No tiene permisos para modificar tags");

        $url = URL_RUTAS . "/tags/$tagId";

        //Obtenemos el nuevo nombre para el TAG del cuerpo de la consulta
        $tag = RequestService::obtenerContenidoCuerpo();

        //Validamos el JSON enviado por el usuario
        if (count($tag) != 1 || array_key_last($tag) != "tag")
        {
            throw new \Exception("JSON no válido", 400);
        }

        //Formateamos el tag para que no tenga espacio ni por alante ni por detras y que este en minúsculas
        $tag["tag"] = trim(strtolower($tag["tag"]));

        $datos = AlfrescoService::put($url, $tag);

        isset($datos->error) ? throw new \Exception($datos->error->briefSummary, 400) : null;

        //Asignamos la ID del Tag modificado a la variable global del auditor
        $GLOBALS["datosAauditor"] = $tagId;

        return ResponseService::respuestaDatos("Tag modificado", 200, $datos);
    }


    /**
	 * Quita un tag a un nodo
     * 
     * @param string $nodoId id del nodo
     * @return array con respuesta 
     * @throws Exception JSON no válido
     * @throws Exception Ese tag no está asignado a ese nodo
	 */
    static function quitarTagNodo($nodoId) 
    {
        AuthService::validarRoles(AuthService::obtenerRolesAdmin(), "No tiene permisos para eliminar tags");
        
        $tag = RequestService::obtenerContenidoCuerpo();
        isset($tag["tag"]) ? null : throw new \Exception("JSON no válido", 400);
        $tag["tag"] = trim(strtolower($tag["tag"]));

        if (count($tag) != 1 || array_key_last($tag) != "tag")
        {
            throw new \Exception("JSON no válido", 400);
        }

        $tags_array = self::obtenerTagsNodo($nodoId, false);

        if ($tag_encontrado = TagService::tagEnArray($tag["tag"], $tags_array))
        {
            $url = URL_RUTAS . "nodes/$nodoId/tags/$tag_encontrado->id";
            AlfrescoService::delete($url);

            $datos = array(
                "tag" => $tag["tag"],
                "id" => $tag_encontrado->id
            );

            //Asignamos la ID del Tag eliminado a la variable global del auditor
            $GLOBALS["datosAauditor"] = $datos["id"];

            return ResponseService::respuestaDatos("Tag eliminado", 200, $datos);
        }
        else
        {
            throw new \Exception("Ese tag no está asignado a ese nodo.", 400);
        }
    }
}  
    
