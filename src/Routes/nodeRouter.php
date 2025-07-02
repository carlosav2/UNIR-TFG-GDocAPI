<?php
// nodeController.php: Router de de la ruta /node
// Carlos Ahumada Vidal

use GDocAPI\Controllers\NodeController;

// RUTAS GET --------------------------------------------------------------------------
// Obtener informacion de un nodo a partir de su id
$router->get("/nodo/{nodoId}", function($nodoId) {
    return NodeController::execute("obtenerInfoNodo", 200, $nodoId);
});

// Buscar id de un nodo a partir de su nombre
$router->get("/nodo/nombre/{nodoName}", function($nodoName) {
    return NodeController::execute("buscarNodo", 200, $nodoName);
});

// Buscar nodo por su nombre (capetas entidad) 
$router->get("/nodo/nombre/{nodoName}/id", function($nodoName) {
    return NodeController::execute("buscarNodoId", 200, $nodoName);
});

// Busca id de un nodo a partir de su nombre y que sea hijo de un nodo entidad (carpeta tipo)
$router->get("/nodo/nombre/{nodoName}/{nodoPadre}", function($nodoName, $nodoPadre) {
    return NodeController::execute("buscarNodoHijo", 200, $nodoName, $nodoPadre);
});

// Obtiene los hijos de un nodo segun su id
$router->get("/nodo/{nodoId}/hijos", function($nodoId){
    return NodeController::execute("obtenerHijosNodo", 200, $nodoId);
});

// Obtiene los hijos de un nodo padre (id) segun un nombre de carpeta
$router->get("/nodo/{identidad}/{nodoName}/hijos", function($identidad, $nodoName) {
    return NodeController::execute("obtenerHijosNodoNombre", 200, $identidad, $nodoName);
});

// RUTAS POST --------------------------------------------------------------------------
$router->post("/nodo/{nodoPadre}/hijo", function($nodoPadre) {
    return NodeController::execute("crearNodo", 200, $nodoPadre);
});