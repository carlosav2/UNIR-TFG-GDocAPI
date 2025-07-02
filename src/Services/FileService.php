<?php
// FileService.php: Clase del servicio para tareas de gestión de ficheros
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

use GDocAPI\Services\AlfrescoService;
use GDocAPI\Services\SiteService;

class FileService
{
    /**
	 * Compone al Form Data que se enviará como cuperpo en la solicitud POST HTTP
     * 
     * @param string $nombre_archivo_post nombre del archivo a enviar
     * @return array con el form data creado 
     * @throws Exception Si faltan metadatos.
	 */
    static function componerFormData ($nombre_archivo_post)
    {
        // Se comprueba si las normas para las validaciones estan definidas
        // y se validan los campos
        if (defined('VALIDACIONES_CAMPOS')) 
        {
            ValidationService::validarDatosPost();
        }

        $nodeType = SiteService::generarNodoTipoDocNodeType();

        // Mapea del POST a variables los parametros seteados en la config del site (YAML)
        foreach (FORM_DATA_POST_PARAMS as $parametro) 
        {
            $$parametro = $_POST[$parametro] ?? "";
        }

        // Si uno de los parámetros es fecha, la setea
        if (isset($fecha))
        {
            if ($fecha != "")
            {
                if (!isset($hora))
                {
                    $hora = "00:00";
                }
                $fecha_date = \DateTime::createFromFormat('d/m/Y', $fecha);
                $fecha_formateada = (string)$fecha_date->format("Y-m-d");
                $fecha_alf = $fecha_formateada . "T" . $hora . ".000+0000";
            }
            else
            {
                $fecha_alf = date("Y-m-d") . "T" . $hora . ".000+0000";
            }
        }

        // Coge la plantilla del nombre del archivo y realiza los remplazos 
        $nombreArchivo = FILE_NOMBRE;

        // Realizamos una interación con el nombre del archivo por cada reemplazo
        foreach (FILE_NOMBRE_REPLACE as $value) 
        {
            // $variable contiene en nombre de la variable
            // $variable_valor contiene el valor definitivo: de $variable/$_POST[$variable]
            $variable = $value[1];

            // Comprueba que el tercer campo contenga una propiedad que sea 'formateos'
            if (isset($value[2]["formateos"]))
            {   
                // Recorre los formateos
                foreach ($value[2] as $formateo)
                {  
                    // Porcesa el texto con el tipo de formato correspondiente
                    $_POST[$variable] = SiteService::procesartxt($formateo, $_POST[$variable]);
                }
            }
        
            // Si la variable es tipo_documento, normaliza su valor según la config del site
            if ($variable == "tipo_documento")
            {
                $variable_valor = SiteService::arreglarNombreArchivo($_POST[$variable]);
            }
            // Sino las asigna directamente
            else
            {
                // Mira primero en el POST, y despues en la variables mapeadas
                $variable_valor = $_POST[$variable] ?? null;

                // Si la variable es una fecha le damos el formato que queremos
                if ($variable == "fecha") 
                {
                    $date = new \DateTime($variable_valor);
                    $variable_valor =  $date->format('Y/m/d');
                }

                if (!$variable_valor)
                {
                    $variable_valor = $$variable ?? null;
                    if (!$variable_valor)
                    {
                        throw new \Exception("Faltan metadatos obligatorios: $variable", 400);
                    }
                }
                // Le quita a la variables los caracteres / y : (para la hora y la fecha del nombre del archivo)
                $variable_valor = str_replace("/","",$variable_valor);
                $variable_valor = str_replace(":","",$variable_valor);
            }

            // Realiza el reemplazo en el nombre del archivo
            $tipo = $value[0];
            $nombreArchivo = str_replace($tipo, $variable_valor, $nombreArchivo);
        }

        // Componemos el data_form. 
        // Obtenemos la plantilla con sus reemplazos
        $datos = FORM_DATA_FILE;

        // Obtenemos los remplazos 
        $datosRemplazo = $datos["reemplazos"];
        
        // Realizamos una iteración en la plantilla por cada reemplazo
        foreach ($datosRemplazo as $value) 
        {    
            // En $variableNombre tendremos el nombre de la variable cuyo valor nos interesa
            // Está en la posición 2 del array de reemplazos
            $variableNombre =  $value[2];
            
            // Miramos si la variable está en el código o en el POST (posición 5 del array)
            // Si es 0 es una variable del código; si es 1 es una variable del POST (las mapeadas por ejemplo)
            if ($value[5] == 0)
            {
                $variable = $$variableNombre;
            }
            else
            {
                $variable = $_POST[$variableNombre];
            }

            // Comprobamos si en el reemplazo se ha definido un caracter separador (posición 3)
            // Y en la posición 4 tenemos con que elemento del explode nos quedamos.
            // Por ejemplo: $variable = 12345678A_PEPITO
            //               si tenemos 0 en la posición 4, nos quedamos solo con 123456789A
            //               si tenemos 1 en la posición 4, nos quedamos solo con PEPITO
            if ($value[3] !== "")
            {
                $variable = explode($value[3], $variable)[$value[4]];
            }

            // Ahora ya tenemos el valor que queremos reemplazar. Miramos si es la propia key o en la hija
            // Si el valor de la posición 1:
            //   - está vacio, se reemplaza el valor de la key
            //   - contiene una key, reemplazaremos el valor de la etiqueta hija cuya key sea ese valor.
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

        // Quitamos los reemplazos del array del forma-data
        unset($datos["reemplazos"]);
        
        // Añadimos el archivo al array del form-data
        $datos["filedata"] = curl_file_create($_FILES["archivo"]["tmp_name"], $_FILES["archivo"]['type'], $nombre_archivo_post);

        return $datos;        
    }


    /**
	 * Envia el archivo
     * 
     * @param string $nodoPadre id del nodo (carpeta) donde se almacenará
     * @return array con el resultado 
     * @throws Exception No se ha adjuntado archivo
	 */
    static function enviarArchivo($nodoPadre)
    {
        if (!isset($_FILES["archivo"]) || $_FILES["archivo"]["error"] != 0)
        {
            throw new \Exception("No se ha enviado ningún archivo", 400);
        }

        $url = URL_RUTAS . "nodes/$nodoPadre/children";

        $nombre_archivo_post = basename($_FILES["archivo"]['name']);


        $formData = self::componerFormData($nombre_archivo_post);

        $resultado = AlfrescoService::postFromData($url, $formData);


        return $resultado;
    }
}