<?php
// tagController.php: Router de de la ruta /tag
// Carlos Ahumada Vidal

use GDocAPI\Controllers\TagController;

// RUTAS GET --------------------------------------------------------------------------
// Obtiene todos los tags
$router->get("/tag", function() {
    return TagController::execute("obtenerTodosTags", 200);
});

// Obtiene los tags de un nodo a partir de su id
$router->get("/tag/nodo/{nodoId}", function($nodoId) {
    return TagController::execute("obtenerTagsNodo", 200, $nodoId);
});

// RUTAS POST --------------------------------------------------------------------------
// Asigna tag a un nodo (tiene que existir previamente el tag)
$router->post("/tag/nodo/{nodoId}", function($nodoId) {
    return TagController::execute("asignarTagNodo", 200, $nodoId);
});
// Asigna tag a un nodo, y si no existe lo crea
$router->post("/tag/nodo/{nodoId}/forzar", function($nodoId) {
    return TagController::execute("asignarTagNodo", 200, $nodoId, true);
});

// RUTAS PUT --------------------------------------------------------------------------
// Modifica el nombre de un tag
$router->put("/tag/{tagId}", function($tagId) {
    return TagController::execute("modificarTag", 200, $tagId);
});

// RUTAS DELETE --------------------------------------------------------------------------
// Elimina un tag de un nodo
$router->delete("/tag/nodo/{nodoId}", function($nodoId) {
    return TagController::execute("quitarTagNodo", 200, $nodoId/*, true*/);
});

