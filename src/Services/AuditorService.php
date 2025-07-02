<?php
// AuditorService.php: Clase del servicio para auditoria
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

use Auditor;
use GDocAPI\Services\SecurityService;

class AuditorService
{
    /**
	 * Audita las acciones sadisfactorias
     * 
     * @return void
	 */
    static function auditar()
    {
        // Recuperamos la variable global;
        global $datosAauditor;
        // Generamos una nueva instancia del auditor
        $auditor = new Auditor("gdocapi", Auditor::LOG2BBDD, Auditor::AUD);
        // Enviamos los datos a la tabla del auditor:
        // (1: Tipo de audición, 2: Ruta de la acción junto con el site y el método, 3: usuario que realiza la acción)
        $auditor->log(Auditor::AUD, request()->getMethod() . "|" . getallheaders()['Site'] . "|" . $_SERVER['REQUEST_URI'] . "|" . $datosAauditor, SecurityService::$globarUsuario);
        // Eliminamos el contenido de la variable global
        unset($GLOBALS["datosAauditor"]);
    }

    
    /**
	 * Audita las acciones erroneas
     * 
     * @return void
	 */
    static function auditarError()
    {
        // Recuperamos la variable global;
        global $datosAauditor;
        // Generamos una nueva instancia del auditor pero con el tipo ERROR
        $auditor = new Auditor("gdocapi", Auditor::LOG2BBDD, Auditor::ERR);
        // Enviamos los datos a la tabla del auditor:
        // (1: Tipo de audición, 2: Ruta de la acción junto con el site y el método, 3: usuario que realiza la acción)
        $auditor->log(Auditor::ERR, request()->getMethod() . "|" . getallheaders()['Site'] . "|" . $_SERVER['REQUEST_URI'] . "|" . $datosAauditor, SecurityService::$globarUsuario);
        // Eliminamos el contenido de la variable global
        unset($GLOBALS["datosAauditor"]);
    }
}