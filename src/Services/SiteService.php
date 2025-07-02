<?php
// SiteService.php: Clase del servicio para tareas del site
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

use Symfony\Component\Yaml\Yaml;

class SiteService
{
    /**
	 * Obtiene el site de la cabecera
     * 
     * @return string con el site
     */
    static function getSite() 
    {
        return getallheaders()["Site"];
    }


    /**
	 * Obtiene el site de la cabecera (en mayúsculas)
     * 
     * @return string con el site
     */
    static function getSiteUp() 
    {
        return strtoupper(getallheaders()["Site"]);
    }


    /**
	 * Gnenera el nombre del nodo IDENTIDAD según el archivo YAML - NODO_TIPO_ID_NOMBRE
     * 
     * @return string con el nombre
     * @throws Eception Faltan parametro obligatorios
     */
    static function generarNodoIdNombre()
    {
        // Cogemos la plantilla con el nombre de la carptea tipo ID
        $tipo = NODO_TIPO_ID_NOMBRE;
        // En la posicíon 0 tenemos el nombre con los valores a sustitir
        $nombre = $tipo[0];
        // Iteramos por cada reemplazo que tengamos seteado en la posición 1
        foreach ($tipo[1] as $value) 
        {
            // Busca el valor de la variable en el POST y la reemplaza en la plantilla
            if (!isset($_POST["$value[1]"]))
            {
                throw new \Exception("Faltan parametro obligatorios: " . $value[1], 400);
            }
            $nombre = str_replace($value[0], $_POST[$value[1]], $nombre);
        }
        return strtoupper($nombre);
    }


    /**
	 * Gnenera el nombre del nodo TIPO_DOC según el archivo YAML - OBTENER_TIPODOC_NOMBRE
     * 
     * @return string con el nombre
     * @throws Eception Parametro necesario no enviado
     * @throws Eception Nombre no encontrado con el tipo de documento proporcionado
     */
    static function generarNodoTipoDocNombre()
    {   
        // Cogemos la plantilla de busqueda con el nombre del nodo tipo carpeta (tipo de documento).
        $config = OBTENER_TIPODOC_NOMBRE;
        
        // En la key parametro tenemos la variable del POST que contiene el valor a buscar
        $tipoDoc = $_POST[$config["parametro"]] ?? throw new \Exception("Parametro necesario no enviado", 400);
        unset($config["parametro"]);
        
        // Si hay un preproceso del valor, se realiza.
        if (isset($config["preproceso"]))
        {
            $tipoDoc = self::procesartxt($config["preproceso"], $tipoDoc);
            unset($config["preproceso"]);
        }

        $nombre = null;
        // Si tiene seteada una correspondencia (tipo) se realiza       
        if (isset($config['tipo']))
        {
            // Busca si está contenido en alguna etiqueta
            foreach ($config['tipo'] as $tipo => $valores) 
            {
                if (is_array($valores) && in_array($tipoDoc, $valores, true)) 
                {
                    $nombre = $tipo; // Retorna el tipo si se encuentra el valor
                }
            }
            // Si no lo encuentra y existe un default lo asigna
            if (!$nombre)
            {
                if (isset($config['tipo']['default']))
                {
                    $nombre = $config['tipo']['default'];
                }
                else
                {
                    throw new \Exception("Nombre no encontrado con el tipo de documento proporcionado: $tipoDoc", 400);
                }
            }
        }
        else
        {
            $nombre = $tipoDoc;
        }
       
        return $nombre;
    }


    /**
	 * Gnenera el nodetype según el archivo YAML - OBTENER_TIPODOC_NODETYPE
     * 
     * @return string con el nodetype
     * @throws Eception Parametro necesario no enviado
     * @throws Eception NodeType no encontrado con el tipo de documento proporcionado
     * @throws Eception El tipo de documento no es válido
     * @throws Eception Plantilla de correspondencias obtener_tipodoc_nodetype mal definida
     */
    static function generarNodoTipoDocNodeType()
    {
        // cogemos la plantilla con el tipo de nodo para asignárselo al archivo. (nodeType, agrupación...)
        $config = OBTENER_TIPODOC_NODETYPE;
        
        // En la key parametro tenemos la variable del POST que contiene el valor a buscar
        $tipoDoc = $_POST[$config["parametro"]] ?? throw new \Exception("Parametro necesario no enviado", 400);
        unset($config["parametro"]);

        // Si hay un preproceso del valor, se realiza.
        if (isset($config["preproceso"]))
        {
            $tipoDoc = self::procesartxt($config["preproceso"], $tipoDoc);
            unset($config["preproceso"]);
        }
        
        // primero se mira si la plantilla de correspondencia es tipo array o diccionario
        switch ($config["tipo"])
        {
            // Si la plantilla de correpondencia está definida como un array
            case "array":
                // Busca la correspondencia (correspondencia) 
                $nodeType = null;
                foreach ($config['correspondencia'] as $correspondencia => $valores) {
                    if (is_array($valores) && in_array($tipoDoc, $valores, true)) {
                        $nodeType = $correspondencia; // Retorna la correspondencia si se encuentra el valor
                    }
                }
                // Si no lo encuentra y existe un default lo asigna
                if (!$nodeType)
                {
                    if (isset($config['correspondencia']['default']))
                    {
                        $nodeType = $config['correspondencia']['default'];
                    }
                    else
                    {
                        throw new \Exception("NodeType no encontrado con el tipo de documento proporcionado: $tipoDoc", 400);
                    }
                }
                break;
           
            // Si la plantilla de correpondencia está definida como un diccionario
            case "diccionario":
                if (isset($config['correspondencia'][$tipoDoc])) 
                {
                    $nodeType = $config['correspondencia'][$tipoDoc];
                }
                else
                {
                    throw new \Exception("El tipo de documento no es válido: $texto", 400);
                }
                break;

            default:
                throw new \Exception("Plantilla de correspondencias obtener_tipodoc_nodetype mal definida.", 400);                
        }
        
        return $nodeType;
    }


    /**
	 * Arregla el nombre del archivo según el archivo YAML - NORMALIZAR_FILE_NOMBRE
     * 
     * @param string $texto con el nombre
     * @return string con el nombre arreglado
     * @throws Eception El tipo de documento no es válido
     * @throws Eception Nombre de TIPO_DOC no válido
     */
    static function arreglarNombreArchivo($texto)
    {
        // cogemos la plantilla que normaliza la parte del nombre que se asignara al archivo.
        $config = NORMALIZAR_FILE_NOMBRE;

        // Si hay un preproceso del valor, se realiza.
        if (isset($config["preproceso"]))
        {
            $texto = self::procesartxt($config["preproceso"], $texto);
        }
        
         
        if (!isset($config["tipo"]))
        {
            $nombre = $texto;
        }
        // Si tiene seteada una correspondencia (tipo) se realiza
        else
        {
            if (isset($config['tipo'][$texto])) 
            {
                // Si hay un postproceso del valor, se realiza.
                if (str_starts_with($config['tipo'][$texto],"<"))
                {
                    $texto = self::procesartxt([str_replace(["<",">"],"",$config['tipo'][$texto])], $texto);
                }
                else
                {
                    $texto = $config['tipo'][$texto];
                }
            }
            else
            {
                throw new \Exception("El tipo de documento no es válido: $texto", 400);
            }
        }
        $nombre = $texto;

        if ($nombre == "")
        {
            throw new \Exception("Nombre de TIPO_DOC no válido: " . $_POST["tipo_documento"], 400);
        }
        return $nombre;
    }


    /**
	 * Quita los acentos de una cadena
     * 
     * @param string $texto cadena de texto
     * @return string cadena tratada
     */
    static function quitarAcentos($texto)
    {
        $acentos    = ['Á', 'É', 'Í', 'Ó', 'Ú', 'á', 'é', 'í', 'ó', 'ú'];
        $sinAcentos = ['A', 'E', 'I', 'O', 'U', 'a', 'e', 'i', 'o', 'u'];
        $cadena = str_replace($acentos, $sinAcentos, $texto);

        return $cadena;
    }


    /**
	 * Realiza un procesado a una cadena de texto
     * 
     * @param string $preproceso proceso a realizar
     * @param string $texto cadena de texto
     * @return string cadena tratada
     */
    static function procesartxt($preproceso, $texto)
    {
        foreach ($preproceso as $accion)
        {
            switch ($accion)
            {
                case "acentos":
                    $texto = self::quitarAcentos($texto);
                    break;

                case "mayusculas":
                    $texto = strtoupper($texto);
                    break;

                case "minusculas":
                    $texto = strtolower($texto);
                    break;

                case "capitalizar":
                    $texto = strtolower($texto);
                    $texto = ucfirst($texto);
                    break;

                default:
                    break;
            }
        }

        return $texto;
    }

    
    /**
	 * Procesa caracteres especiales de una cadena de texto
     * 
     * @param string $texto cadena de texto
     * @return string cadena tratada
     */
    static function procesarCaracteresEspecialesUrl($texto)
    {
        // Array que contiene los codigos con sus letras correspondientes cuando se escriben tildes en la URL
        $caracteres = array(
            "%C3%A1" => "á", "%C3%81" => "Á",
            "%C3%A9" => "é", "%C3%89" => "É",
            "%C3%AD" => "í", "%C3%8D" => "Í",
            "%C3%B3" => "ó", "%C3%93" => "Ó",
            "%C3%BA" => "ú", "%C3%9A" => "Ú"
        );

        foreach ($caracteres as $codCaracter => $caracter)
        {
            $texto = str_replace($codCaracter, $caracter, $texto);
        }

        return $texto;
    }


    /**
	 * Construye una cadena JSON basada en configuraciones definidas en el YAML - BUSQUEDA
     * Se usa para busquedas de metadatos
     * 
     * @param string $nombrePlantilla plantilla a usar de BUSQUEDA
     * @return string con el resultado de la busqueda
     * @throws Exception La plantilla no existe
     * @throws Exception Debe proporcionase al menos un parámetro correto de búsqueda
     */
    static function funcionBusquedaYaml($nombrePlantilla)
    {
        !array_key_exists($nombrePlantilla, BUSQUEDA) ? throw new \Exception("La plantilla $nombrePlantilla no existe", 400) : null; 

        $body = RequestService::obtenerContenidoCuerpo();
        
        // Obtiene la plantilla de busqueda según el parametro de la ruta
        $arraySalida = BUSQUEDA[$nombrePlantilla]["plantilla"];
        
        // Cantidad de inserciones. Controlar que al menos exista 1 en el body para evitar sobrecarga en
        // busquedas muy genéricas.
        $cantidad = 0;
        
        foreach(BUSQUEDA[$nombrePlantilla]["inserciones"] as $insercion)
        {
            // Permite que los keys del body sean opcionales (campos de buqueda opcionales)
            if (isset($body[$insercion[3]]))
            {
                // <>: Sustituye por un valor del body
                strpos($insercion[2],"<>") ? $texto =  str_replace("<>", $body[$insercion[3]], $insercion[2]) : null;
                $arraySalida[$insercion[0]][][$insercion[1]] = $texto;
                $cantidad++;
            }
            
            // {}: sustituye por un valor de las constantes mapeadas del YAML
            if (strpos($insercion[2],"{}"))
            {
                $texto =  str_replace("{}", constant($insercion[3]), $insercion[2]);
                $arraySalida[$insercion[0]][][$insercion[1]] = $texto;
            }
        }

        // Si no hay parametros de busqueda correctos en el body se devuelve error
        $cantidad > 0 ? null : throw new \Exception("Debe proporcionase al menos un parámetro correto de búsqueda", 400); 

        return $arraySalida;
    }

    
    /**
	 * Resuelve las referencias presentes en un archivo YAML, sustituyendo los marcadores 
     * del tipo {clave} por su correspondiente valor definido en el mismo archivo.
     * 
     * @param string $site_tipo nombre del site
     * @return array con la configuración
     */
    static function resuelveReferenciasYaml($site_tipo)
    {
        $config = Yaml::parseFile(__DIR__ . "/../../config/site_$site_tipo.yaml");
        foreach ($config as $key => &$value) 
        {
            if (is_string($value)) 
            {
                // Buscar y reemplazar referencias del tipo {clave}
                $value = preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($config) {
                    $placeholder = $matches[1]; // Captura la clave dentro de las llaves
                    return $config[$placeholder] ?? ''; // Reemplaza con el valor o vacío si no existe
                }, $value);
            }
        }
        return $config;
    }


    /**
	 * Esta función toma un array de configuración y lo mapea como constantes PHP. 
     * Las claves del array se transforman en constantes globales, si no están definidas previamente.
     * 
     * @param array $config configuración con las referencias resueltas
     * @return void
     */
    static function mapearConfigAConstantes(array $config)
    {
        foreach ($config as $key => $value) 
        {
            if (!defined($key)) 
            { 
                define(strtoupper($key), $value);
            }
        }
    }
}    