<?php
// docController.php: Router de de la ruta /doc
// Carlos Ahumada Vidal

$router->get("/documentacion", function() {
    header("Location: ../public/doc/swagger-ui/dist/");
});

$router->get("/documentacion/editar", function() {
    header("Location: ../doc/swagger-editor/");
});