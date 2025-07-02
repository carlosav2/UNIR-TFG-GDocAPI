<?php
// FileController.php: Clase controlador de las rutas fileRouter
// Carlos Ahumada Vidal

namespace GDocAPI\Controllers;

use GDocAPI\Controllers\Controller;
use GDocAPI\Controllers\TagController;
use GDocAPI\Services\NodeService;
use GDocAPI\Services\SiteService;
use GDocAPI\Services\FileService;
use GDocAPI\Services\ResponseService;
use GDocAPI\Services\AlfrescoService;
use GDocAPI\Services\AuthService;
use GDocAPI\Services\CacheService;

class FileController extends Controller
{
    /**
	 * Sube un fichero a Alfresco
     * 
     * @return array con respuesta 
     * @throws Exception JSON no válido
     * @throws Exception Existe más de una carpeta tipo_documento con el identificador
     * @throws Exception Respuesta de error de Alfresco
	 */
    static function subirFichero()
    {
        AuthService::validarRoles(AuthService::obtenerRolesEdicion(), "No tiene permisos para subir archivos");

        $identidad = $_POST['identidad'] ?? null;
        $doc_tipo = $_POST['tipo_documento'] ?? null;

        if (!$identidad || !$doc_tipo)
        {
            throw new \Exception("JSON no válido", 400);
        }

        $nodoIdNombre = SiteService::generarNodoIdNombre();
        $nodoTipoDocNombre = SiteService::generarNodoTipoDocNombre();
        //$nodoTipoDocNodeType = SiteService::generarNodoTipoDocNodeType();
        $carpetaId = NodeService::buscarNodo($identidad, NODO_RAIZ_ID);
        $count = $carpetaId->list->pagination->count;
        
        // No existe el nodo IDENTIDAD, lo crea (o rescata de cache)
        if ($count == 0)
        {
            // buscamos primero si lo tenemos en cache (pos si está pendiente de indexar en alfresco)
            $cache = CacheService::getCache("id|$identidad");

            if ($cache != "")
            {
                $existeNodoCache = NodeService::existeNodo($cache);
            }

            if ($cache == "" || !$existeNodoCache)
            {
                $carpetaId = NodeService::crearNodo($nodoIdNombre, NODO_RAIZ_ID, "NODO_TIPO_ID");
                $carpetaId = $carpetaId->id;
                // Metemos en cache el nodo creado para poder usarlo mientras se indexa
                CacheService::setCache("id|$identidad", $carpetaId);
            }
            else
            {
                $carpetaId = $cache;
            }
        }
        else
        {
            $carpetaId = $carpetaId->list->entries[0]->entry->id;
        }
        // Si en la config del site está seteado, se normaliza en nombre de la varible de POST con el nombre
        // que tenga la carpeta ID (para cuando ya exista)
        NodeService::normalizaNombrePost($carpetaId);

        // TIPO DOC
        $textoBusqueda = str_replace("-"," ", $nodoTipoDocNombre);
        $carpetaTipoDoc = NodeService::buscarNodo($textoBusqueda, $carpetaId);
        $count = $carpetaTipoDoc->list->pagination->count;
        if ($count > 1)
        {
            throw new \Exception("Existe más de una carpeta tipo_documento con el identificador: $nodoTipoDocNombre");
            die;
        }

        if ($count == 0)
        {
            // buscamos primero si lo tenemos en cache (porque estraá pendiente de indexar en alfresco)
            $cache = CacheService::getCache("doc|$identidad|$textoBusqueda");

            if ($cache != "")
            {
                $existeNodoCache = NodeService::existeNodo($cache);
            }

            if ($cache == "" || !$existeNodoCache)
            {
                $carpetaTipoDoc = NodeService::crearNodo($nodoTipoDocNombre, $carpetaId, "NODO_TIPO_CARPETA");
                $carpetaTipoDoc = $carpetaTipoDoc->id;
                // Metemos en cache el nodo creado para poder usarlo mientras se indexa
                CacheService::setCache("doc|$identidad|$textoBusqueda", $carpetaTipoDoc);
            }
            else
            {
                $carpetaTipoDoc = $cache;
            }
        }
        else
        {
            $carpetaTipoDoc = $carpetaTipoDoc->list->entries[0]->entry->id;
        }

        $respuesta = FileService::enviarArchivo($carpetaTipoDoc);

        if (isset($respuesta->error))
        {
            throw new \Exception($respuesta->error->errorKey, $respuesta->error->statusCode);
        }
        $respuesta_array = (array)$respuesta->entry;

        if (isset($_POST["tag"]))
        {
            $tag = $_POST["tag"];
            TagController::asignarTagNodo($respuesta->entry->id, true, true);
        }

        //Declaramos un variable global para lamacenar información extra para subir al auditor
        $GLOBALS["datosAauditor"] = $respuesta->entry->id;

        return ResponseService::respuestaDatos("Archivo subido con éxito", 200, $respuesta);

    }


    /**
	 * Descarga un archivo
     * 
	 * @param string $idNodo con la id del nodo a descargar
     * @return binary archivo en binario
	 */
    static function descargarArchivo($idNodo)
    {
        AuthService::validarRoles(AuthService::obtenerRolesBusqueda(), "No tiene permisos para descargar archivos");

        $url = URL_RUTAS . "nodes/$idNodo/content";
        $datos = AlfrescoService::getFile($url);
        die;
    }


    /**
	 * Descarga documentos PDF en un ZIP en base a un funcion de busqueda segun plantilla YAML
     * 
	 * @param string $pantilla plantilla para la busqueda del archivo
     * @return array con archivo ZIP 
	 */
    static function descargarDocumentosPDF($plantilla)
    {
        // Comprueba los permisos
        AuthService::ValidarRoles(AuthService::obtenerRolesBusqueda(), "No tienes permisos para descargar archivos");

        //Recoge la funcion de busqueda establecida en YAML y la ejecuta
        $datosDeBusqueda = SiteService::FuncionBusquedaYaml($plantilla);
        $arrayResultado = json_decode(json_encode(AlfrescoService::post(URL_BUSCAR, $datosDeBusqueda)), true);
        $arrayCount = $arrayResultado["list"]["entries"];

        //Si existen datos continua ejecutando
        if (count($arrayCount) > 0)
        {
            $listIds = [];
            // Procesa cada documento del array
            foreach ($arrayCount as $i => $documento) 
            {
                $listIds["nodeIds"][] = $documento["entry"]["id"];
            }

            //Se crea el .zip y empieza a descargar
            $zip = AlfrescoService::post(URL_RUTAS . "/downloads", $listIds);
            $zip = $zip->entry->id;

            //Empieza a descargar el archivo esperando a que esté listo el ZIP por parte de Alfresco
            $tiempoMax = ZIP_TIEMPO_MAX * 1000000;
            $tiempoEjecucion = ZIP_TIEMPO_ESPERA * 1000000;
            do 
            {
                $respuesta = AlfrescoService::post(URL_RUTAS . "/nodes/" . $zip, "");
                $resp_array = json_decode(json_encode($respuesta), true);
                if ($resp_array["entry"]["properties"]["download:status"] == "DONE") break;
                usleep($tiempoEjecucion);
                $tiempoMax -= $tiempoEjecucion;
            } while ($tiempoMax < 0);

            //Si no ha dado timeout devuelve el .zip
            if ($tiempoMax > 0)
            {
                $salida = AlfrescoService::getFile(URL_RUTAS . "/nodes/" . $zip . "/content");
                return ResponseService::respuestaDatos("La descarga ha finalizado correctamente", 200, "OK");
            }
            else
            {
                return ResponseService::respuestaDatos("La descarga no se ha completado", 402, "Tiempo de espera agotado");
            }
        }
        return ResponseService::respuestaDatos("La consulta no devuelve ningun valor", 204, "No Content");
    }


    /**
	 * Descarga los metadatos de los documetnos resultado de una busqueda segun plantilla YAML
     * 
	 * @param string $pantilla plantilla para la busqueda del archivo
     * @return array con metadatos 
	 */
    static function descargarDocumentosMetaData($plantilla)
    {
        // Comprueba los permisos
        AuthService::ValidarRoles(AuthService::obtenerRolesBusqueda(), "No tienes permisos para descargar archivos");

        // Se piden los datos de busqueda para poder conseguir los nodos a descargar
        $datosDeBusqueda = SiteService::funcionBusquedaYaml($plantilla);
        $arrayResultado = AlfrescoService::post(URL_BUSCAR, $datosDeBusqueda);
        $arrayResultado = $arrayResultado->list->entries;

        // Se comprueba si existen nodos
        if (count($arrayResultado) > 0)
        {
            $salida = [];
            //Se obtienen los metadatos de cada nodo y se concatenan con los demas
            foreach($arrayResultado as $i => $documento)
            {
                $nodoName = $documento->entry->id;
                $salida[$i] = NodeService::obtenerInfoNodo($nodoName);
            }

            //Se devuelve el json
            return ResponseService::respuestaDatos("La consulta es correcta", 200, $salida);
        }

        return ResponseService::respuestaDatos("La consulta no devuelve ningun valor", 204, "No Content");
    }


    /**
	 * Elimina un archivo de Alfresco
     * 
	 * @param string $nodoId id del nodo a eliminar
     * @return json con respuesta 
     * @throws Exception con el error de Alfresco
	 */
    static function eliminarArchivo($nodoId)
    {
        AuthService::validarRoles(AuthService::obtenerRolesAdmin(), "No tiene permisos para eliminar archivos");

        $url = URL_RUTAS . "nodes/$nodoId";

        $resultado = AlfrescoService::delete($url);

        if (isset($resultado->error))
        {
            throw new \Exception("Alfreco dice: " . $resultado->error->briefSummary, 400);
        }

        //Asignamos a la variable global el ID del archivo eliminado
        $GLOBALS["datosAauditor"] = $nodoId;

        return ResponseService::respuestaDatos("Archivo borrado con éxito", 200, "OK");
    }
}
