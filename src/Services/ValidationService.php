<?php
// ValidationService.php: Clase del servicio para validar cadenas
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

class ValidationService
{
    /**
	 * Valida los datos del post segun lo definido en el YAML - VALIDACIONES_CAMPOS
     * 
     * @return void
     * @throws Exception La identidad no es valida
     * @throws Exception Formato de no declarado
     * @throws Exception El campo no es valido
     */
    static function validarDatosPost()
    {
        // Recorre todos campos que se tiene que validar y obtiene sus propiedades para la validación
        foreach (VALIDACIONES_CAMPOS as $clave => $propiedades)
        {
            // Comprueba si la propiedad "tipo" esta declarada
            if (isset($propiedades['tipo']))
            {
                switch ($propiedades['tipo'])
                {
                    // Comprueba si es de tipo "NIF/NIE"
                    case "NIF/NIE":
                        // Comprueba si el formato corresponde al de un NIF y de serlo lo valida
                        if (preg_match("/^[0-9]{8}[A-Z]$/", $_POST[$clave]))
                        {
                            self::validarNIF($_POST[$clave]);
                        }
                        // Comprueba si el formato corresponde al de un NIE y de serlo lo valida
                        elseif (preg_match("/^[A-Z]{1}[0-9]{7}[A-Z]$/", $_POST[$clave]))
                        {
                            self::validarNIE($_POST[$clave]);
                        }
                        // Si no corresponde con nigún formato lanza una excepción para la identidad
                        else
                        {
                            throw new \Exception("La identidad no es valida", 401);
                        }    
                        break;
                    
                    // Comprueba si es de tipo TIME
                    case "TIME":
                        // Valida que la propiedad del formato este declarada
                        if (!isset($propiedades["formato"]))
                        {
                            throw new \Exception("Formato de $_POST[$clave] es necesario", 401);
                        }

                        // Si el campo enviado esta vacia se setea con la fecha u hora actual
                        if (!$_POST[$clave])
                        {
                            $_POST[$clave] = date($propiedades["formato"]);
                        }

                        // Valida que el campo sea correcto
                        self::validarCamposTiempo($_POST[$clave], $propiedades["formato"]);
                        break;
                }
            }

            // Comprueba si la propiedad "expresion_relugar" esta declarada
            if (isset($propiedades['expresion_regular']))
            {
                // Valida que el campo que se esta validando cumpla la estructura de la expresión regular
                if (!preg_match($propiedades["expresion_regular"], $_POST[$clave]))
                {
                    throw new \Exception("El campo $clave no es valido", 401);
                }
            }
        }
    }


    /**
	 * Valida un NIF
     * 
     * @param string $nif NIF a comprobar
     * @return void
     * @throws Exception La identidad no es valida
     */
    static function validarNIF($nif) 
    {
        // Se define un array con todas las letras de los NIFs y sus indices
        $nif_letras = array( 0 => "T", 1 => "R", 2 => "W", 3 => "A", 4 => "G", 5 => "M", 
                             6 => "Y", 7 => "F", 8 => "P", 9 => "D", 10 => "X", 11 => "B", 
                             12 => "N", 13 => "J", 14 => "Z", 15 => "S", 16 => "Q", 17 => "V", 
                             18 => "H", 19 => "L", 20 => "C", 21 => "K", 22 => "E" );

        // Se obtiene el número del NIF
        $num_nif = substr($nif, 0, -1);
        // Se obtiene la letra del NIF
        $letra = substr($nif, -1);

        // Se saca el indice de la letra que le sorresponde al NIF
        $res = $num_nif % 23;

        // Valida si la letra obtenida con el indice no coincide con la del NIF enviado
        if ($nif_letras[$res] != $letra)
        {
            throw new \Exception("La identidad no es valida", 401);
        }
    }


    /**
	 * Valida un NIE
     * 
     * @param string $nif NIE a comprobar
     * @return void
     * @throws Exception La identidad no es valida
     */
    static function validarNIE($nie)
    {
        // Se obtiene la letra principal del NIE
        $letra = substr($nie, 0, 1);
        // Se obtiene el resto del contenido que correspondria con el NIF
        $nif = substr($nie, 1);

        // Se valida el NIF obtenido
        self::validarNIF($nif);

        // Valida si la letra principal del NIE es correcta
        if (!in_array($letra, ["X", "Y", "Z"]))
        {
            throw new \Exception("La identidad no es valida", 401);
        }
    }
    
    
    /**
	 * Valida campos de tiempo (fecha, hora)
     * 
     * @param string $tiempo dato a comprobar
     * @param string $formato con el que validar
     * @return void
     * @throws Exception La fecha no es valida
     * @throws Exception La hora no es valida
     */
    static function validarCamposTiempo($tiempo, $formato)
    {
        // Crea un objeto DateTime que contiene la fecha o la hora con el formato indicado
        $tiempoValida = \DateTime::createFromFormat($formato, $tiempo);

        // Comprueba si es una fecha
        if (preg_match("/^[0-9]{2}(\/)[0-9]{2}(\/)[0-9]{4}$|^[0-9]{2}(\/)[0-9]{2}$|^[0-9]{2}$|^[0-9]{4}$/", $tiempo))
        {
            // Valida que la fecha sea correcta
            if (!$tiempoValida || $tiempoValida->format($formato) != $tiempo || $tiempo > date($formato))
            {
                throw new \Exception("La fecha no es valida", 401);
            }
        }

        // Valida que la hora sea correcta
        if (!$tiempoValida || $tiempoValida->format($formato) != $tiempo)
        {
            throw new \Exception("La hora no es valida", 401);
        }
    }
}