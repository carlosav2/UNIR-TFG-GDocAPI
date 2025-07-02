<?php
// AlfrescoService.php: Clase del servicio para tareas sobre Alfresco
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

class AlfrescoService
{
    /**
	 * Realiza GET contra API de Alfresco
     * 
	 * @param string $url a atacar
     * @return array con respuesta 
     * @throws Exception Si ocurre un error en la comunicación con Alfresco.
	 */
    static function get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, ALFRESCO_USER.":".ALFRESCO_PASSW);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: '.ORIGIN, 'Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $resultado = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($resultado === false) 
        {
            throw new \Exception("Error en el servidor de Alfresco", 500);
            die;
        }

        $res = json_decode($resultado);
        return $res;
        
    }


     /**
	 * Realiza POST contra API de Alden para comprobar/crear carpetas y obtener ids
     * 
	 * @param string $url a atacar
	 * @param array $datos del post
     * @return array con respuesta 
     * @throws Exception Si ocurre un error en la comunicación con Alfresco.
	 */
    static function post($url, $datos)
    {   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, ALFRESCO_USER.":".ALFRESCO_PASSW);
        if ($datos)
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $datos ));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: '.ORIGIN, 'Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $resultado = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($resultado === false) 
        {
            throw new \Exception("Error en el servidor de Alfresco", 500);
            die;
        }
        
        $res = json_decode($resultado);
        
        return $res;
    }


     /**
	 * Realiza POST contra API de Alden para comprobar/crear carpetas y obtener ids
     * 
	 * @param string $url a atacar
	 * @param array $datos del post
     * @return array con respuesta 
     * @throws Exception Si ocurre un error en la comunicación con Alfresco.
	 */
    static function postFromData($url, $datos)
    { 
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, ALFRESCO_USER.":".ALFRESCO_PASSW);
        if ($datos)
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $datos);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: '.ORIGIN, 'accept: multipart/form-data','content-type: multipart/form-data; boundary=----WebKitFormBoundary'.md5(time()), 'cache-control: no-cache'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      
        $resultado = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($resultado === false) {
            throw new \Exception("Error en el servidor de Alfresco", 500);
            die;
        }

        $res = json_decode($resultado);
        return $res;
    }

    
     /**
	 * Realiza GET contra API de Alden para descarga archivo
     * 
	 * @param string $url a atacar
     * @return array con archivo 
     * @throws Exception Si ocurre un error en la comunicación con Alfresco.
	 */
    static function getFile($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, ALFRESCO_USER.":".ALFRESCO_PASSW);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: '.ORIGIN, 'Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $resultado = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($resultado === false) {
            throw new \Exception("Error en el servidor de Alfresco", 500);
            die;
        }

        echo $resultado;
    }


    /**
	 * Realiza DELETE contra API de Alfresco
     * 
	 * @param string $url a atacar
     * @return array con respuesta 
     * @throws Exception Si ocurre un error en la comunicación con Alfresco.
	 */
    static function delete($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, ALFRESCO_USER.":".ALFRESCO_PASSW);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: '.ORIGIN, 'Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $resultado = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);

        if ($resultado === false) {
            throw new \Exception("Error en el servidor de Alfresco", 500);
            die;
        }

        $res = json_decode($resultado);
        return $res;
    }


    /**
	 * Realiza la consulta PUT contra la API de Alfresco
     * 
	 * @param string $url a atacar
     * @param array $datos de la solicitud
     * @return array con respuesta 
     * @throws Exception Si ocurre un error en la comunicación con Alfresco.
	 */
    static function put($url, $datos)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_USERPWD, ALFRESCO_USER.":".ALFRESCO_PASSW);
        if ($datos)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $datos ));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: '.ORIGIN, 'Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $resultado = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($resultado === false) {
            throw new \Exception("Error en el servidor de Alfresco", 500);
            die;
        }

        $res = json_decode($resultado);
        return $res;
    }
}