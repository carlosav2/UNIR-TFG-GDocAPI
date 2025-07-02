<?php
// app.php: Puesta en marcha de LUMEN
// Carlos Ahumada Vidal

// Creamos la aplicación
$app = new Laravel\Lumen\Application(dirname(__DIR__));

// Cargar el archivos de rutas
$app->router->group([], function($router) {
    require __DIR__ . '/../src/Routes/tagRouter.php';
    require __DIR__ . '/../src/Routes/authRouter.php';
    require __DIR__ . '/../src/Routes/nodeRouter.php';
    require __DIR__ . '/../src/Routes/fileRouter.php';
    require __DIR__ . '/../src/Routes/docRouter.php';
    require __DIR__ . '/../src/Routes/soapTagRouter.php';
});

// Manejador para rutas no encontradas
$app->router->get('{any:.*}', function () {
    return response()->json([
        'status' => "error",
        'message' => 'Ruta no encontrada',
    ], 404);
});

// Cargamos la config del archivo config/app.php
$app->configure('app');

return $app;