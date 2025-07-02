<?php
// NodeService.php: Clase del servicio para tareas sobre nodos
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

use GDocAPI\Services\AlfrescoService;
use GDocAPI\Services\SiteService;

class NodeService 
{
    /**
	 * Compruena si existe un nodo
     * 
     * @param string $idNodo id del nodo a comprobar
     * @return bool true si existe; false no existe 
	 */
    static function existeNodo($idNodo) 
    {
        $url = URL_RUTAS . "nodes/" . $idNodo;

        $datos = AlfrescoService::get($url);

        if (isset($datos->error))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    

    /**
	 * Obtiene todos los metadatos del nodo
     * 
     * @param string $idNodo id del nodo
     * @return array con los datos 
     * @throws Exception Si se produce un error
	 */
    static function obtenerInfoNodo($idNodo) 
    {
        $url = URL_RUTAS . "nodes/" . $idNodo;

        $datos = AlfrescoService::get($url);

        if (isset($datos->error))
        {
            throw new \Exception($datos->error->errorKey , 400);
        }

        return $datos->entry;
    }


    /**
	 * Obtiene los hijos de un nodo
     * 
     * @param string $idNodo id del nodo
     * @return array con los datos 
     * @throws Exception Si se produce un error
	 */
    static function obtenerHijosNodo($idNodo) {

        $url = URL_RUTAS . "nodes/" . $idNodo . "/children";

        $datos = AlfrescoService::get($url);

        if (isset($datos->error))
        {
            throw new \Exception($datos->error->errorKey , 400);
        }

        return $datos;
    }


    /**
	 * Busca un nodo por nombre dentro de un nodo padre
     * 
     * @param string $nodoNombre nombre del nodo
     * @param string $nodoPadre id del nodo padre (opcional)
     * @return array con los datos 
     * @throws Exception El nombre del nodo está vacio
     * @throws Exception El nodo padre no existe
	 */
    static function buscarNodo($nodoNombre, $nodoPadre = "")
    {
        if ($nodoNombre === "") 
        {
            throw new \Exception("El nombre no puede estar vacío", 400);
        }
        if ($nodoPadre !== "")
        {
            if (!self::existeNodo($nodoPadre))
            {
                throw new \Exception("El nodo padre no existe", 400);   
            }
        }

        $url = URL_BUSCAR;
        $path = PATH;

        // Prepara el cuerpo para la consulta
        $datos = array(
            "query" => array (
                "query" => "((cm:name:*$nodoNombre))",
                "language" => "afts"
            ),
            "filterQueries" => array(
                ["query" => "PATH:'". $path ."'"],
                ["query" => "TYPE:'cm:folder'"]
            ),
            "paging" => array(
                "maxItems" => BUSQUEDA_MAX,     
                "skipCount" => 0       
            )
        );

        // Si se proporciona nodo padre
        if ($nodoPadre !== "")
        {
            array_push($datos["filterQueries"], ["query" => "PARENT:'workspace://SpacesStore/$nodoPadre'"]);
        }
        
        $respuesta = AlfrescoService::post($url, $datos);
        return $respuesta;
    }


    /**
	 * Crea un nodo dentro de un nodo padre
     * 
     * @param string $nodoNombre nombre del nodo
     * @param string $nodoPadre id del nodo padre
     * @param string $nodoTipo tipo de nodo (IDENTIDAD O TIPO_DOC)
     * @return array con los datos del resultado
     * @throws Exception El nombre no puede estar vacío
     * @throws Exception JSON no valido
     * @throws Exception Tipo de nodo no válido
     * @throws Exception El nodo padre no existe
     * @throws Exception Error al crear el nodo
	 */
    static function crearNodo($nodoNombre, $nodoPadre, $nodoTipo)
    {
        if ($nodoNombre == "") 
        {
            throw new \Exception("El nombre no puede estar vacío", 400);
        }
        if (!$nodoPadre || !$nodoTipo)
        {
            throw new \Exception("JSON no valido", 400);
        }

        if (!in_array($nodoTipo, NODO_TIPOS))
        {
            throw new \Exception("Tipo de nodo no válido", 400);
        }

        if (!self::existeNodo($nodoPadre))
        {
            throw new \Exception("El nodo padre no existe", 400);   
        }

        // Prepara la ruta de Alfresco
        $url = URL_RUTAS . "nodes/$nodoPadre/children";
        
        // Obtiene la plantilla del nodo de la configuración del site
        $datos = constant($nodoTipo);

        // Obtenemos los reemplazos que hay que realizar
        $datosRemplazo = $datos["reemplazos"];
        // Iteramos la plantilla para cada reemplazo
        foreach ($datosRemplazo as $value) {
            // En la posición 2 tenemos el nombre de la variable que contiene el valor
            $variableNombre =  $value[2];
            // Objenemos el valor de esa varible
            $variable = $$variableNombre;

            // En la posición 3 tenemos el caracter para dividirlo 
            // En la 4 tenemos el trozo del explode con el que nos quedamos.
            // Por ejemplo, si en 3 tenemos _, y en 4 tenemos 1, para
            // 12345678A_MARCO obtenemos -> MARCO
            if ($value[3] !== "")
            {
                $variable = explode($value[3], $variable)[$value[4]];
            }

            // En la posicón 0 tenemos la key a la que asignamos el valor de la variable
            // salvo que sea una key hija, que estará seteada en la posición 1.
            if ($value[1] == "")
            {
                $datos[$value[0]] = $variable;
            }
            else
            {
                $etiqueta = $value[1];
                $datos[$value[0]][$etiqueta] = $variable;
            }
        }
        // Quitamos del array
        unset($datos["reemplazos"]);

        $respuesta = AlfrescoService::post($url, $datos);

        if (isset($respuesta->error))
        {
            throw new \Exception("Alfresco dice: " . $respuesta->error->errorKey, 400);
        }
        else
        {
            return $respuesta->entry;
        }
    }


    /**
	 * Normaliza el nombre del nodo para el post
     * 
     * @param string $nodoId id del nodo
     * @return void
	 */
    static function normalizaNombrePost($nodoId)
    {
        $datos = NOMBRE_EN_NODO_ID;

        if ($datos[0] == 1)
        {
            $info = self::obtenerInfoNodo($nodoId);
            $nodo_nombre = $info->name;
            $_POST[$datos[1]] = explode($datos[2], $nodo_nombre)[$datos[3]];
        }
    }


    /**
	 * Obtiene el nombre completo del tipo de documento
     * 
     * @param string $nodoName nombre del nodo
     * @return string nombre del nodo
	 */
    static function obtenerNombreTipoDocCompleto($nodoName)
    {
        // Procesamos el nombre del nodo proveniente de la URL para que tenga el formato que queremos
        $nodoName = SiteService::procesarCaracteresEspecialesUrl($nodoName);
        $nodoName = SiteService::procesartxt(['acentos', 'minusculas'], $nodoName);

        $nombreNodo = "";
        $iterador = 1;
        foreach (OBTENER_TIPODOC_NOMBRE["tipo"] as $tipoCarpeta => $valores)
        {
            // Obtenemos unicamente el nombre del tipo de carptea sin caracteres adicionales
            preg_match('/[a-zA-ZáéíóúÁÉÍÓÚ\s]+/', $tipoCarpeta, $coincidencias);
            // Procesamos el nombre del nodo proveniente del SITE para que tenga el formato que queremos
            $coincidencias[0] = SiteService::procesartxt(['acentos', 'minusculas'], $coincidencias[0]);

            // Hacemos la comparación solo con los nombres exactos
            if ($coincidencias[0] == str_replace('%20', ' ', $nodoName))
            {
                $nombreNodo = $tipoCarpeta;
                break;
            }
            $iterador++;
        }

        if ($nombreNodo == "")
        {
            throw new \Exception("El nombre del nodo no se ha encontrado", 401);
        }

        return $nombreNodo;
    }

}