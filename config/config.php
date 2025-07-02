<?php
// config.php: Archivo de carga de componenetes de GDocAPI
// Carlos Ahumada Vidal

use GDocAPI\Services\ResponseService;
use GDocAPI\Services\SecurityService;
use GDocAPI\Services\SiteService;


// Ruta base de la aplicación
const APP_RUTA = "gdocapi";
const BASE_RUTA = "/".APP_RUTA."/public/";
const RUTAS_DOCUMENTACION = [BASE_RUTA."documentacion", BASE_RUTA."documentacion/editar"];
const RUTAS_PUBLICAS = [BASE_RUTA."auth",BASE_RUTA."soap/wsdl", ...RUTAS_DOCUMENTACION];


// Incluimos el entorno 
include __DIR__ . "/path/".APP_RUTA."/file.php";


// Incluimos librerias
require __DIR__ . '/../vendor/autoload.php';

// Incluimos el Auditor
require __DIR__ . '/../../auditor/Auditor.php';

// Comprobamos autorización
try 
{
    SecurityService::validaCORS();
    SecurityService::validaToken();
}
catch (Exception $e)
{
    header('Content-Type: application/json');
    http_response_code($e->getCode());
    echo json_encode(ResponseService::respuestaError($e->getMessage(), $e->getCode()));
    die;
}


$site_tipo = getallheaders()["Site"] ?? null;
$esRutaDocumentacion = in_array($_SERVER['REQUEST_URI'], RUTAS_DOCUMENTACION);

if (!$site_tipo && !$esRutaDocumentacion)
{
    header('Content-Type: application/json');
    echo json_encode(ResponseService::respuestaError("No se ha especificado el site", 400));
    die;
}

if (!$esRutaDocumentacion)
{
    // Incluimos configuración del site: yaml de config y mapeamos a constantes
    $config_site = (SiteService::resuelveReferenciasYaml($site_tipo));
    SiteService::mapearConfigAConstantes($config_site);
}
