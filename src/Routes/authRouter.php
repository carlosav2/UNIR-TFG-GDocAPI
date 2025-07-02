<?php
// authController.php: Router de de la ruta /auth
// Carlos Ahumada Vidal

use GDocAPI\Controllers\AuthController;

// RUTAS POST --------------------------------------------------------------------------
// Autenticación 
$router->post("/auth", function() {
    return AuthController::execute("obtenerToken", 200);
});

$router->put("/auth", function() {
    return AuthController::execute("renovarToken", 200);
});