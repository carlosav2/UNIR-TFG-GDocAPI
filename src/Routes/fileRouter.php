<?php
// fileController.php: Router de de la ruta /file
// Carlos Ahumada Vidal

use GDocAPI\Controllers\FileController;

// RUTAS GET --------------------------------------------------------------------------
// Funcion para descargar un archivo en concreto  
$router->get("/file/{nodoId}", function($nodoId) {
    return FileController::execute("descargarArchivo", 200, $nodoId);
});

// RUTAS POST --------------------------------------------------------------------------
// Subir ficheros
$router->post("/file", function () {
    return FileController::execute("subirFichero", 200);
});

// Descargar varios documentos PDF en un .zip
$router->post("/file/pdf/{plantilla}", function($plantilla) {
    return FileController::execute("descargarDocumentosPDF", 200, $plantilla);
});

// Descargar los metadatos de varios documentos en un .json
$router->post("/file/metadata/{plantilla}", function($plantilla) {
    return FileController::execute("descargarDocumentosMetaData", 200, $plantilla);
});

// RUTAS DELETE --------------------------------------------------------------------------
// Eliminar un nodo tipo file 
$router->delete("/file/{nodoId}", function($nodoId) {
    return FileController::execute("eliminarArchivo", 200, $nodoId);
});