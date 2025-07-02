<?php
// app.php: Configuración Lumen de la aplicación
// Esta configuración se cargará desde el archivo bootstrap\app.php
// Carlos Ahumada Vidal

switch (PRO) 
{
    case 1:
        $debug = false;
        $env = 'production';
        break;
    case 0:
        $debug = true;
        $env = 'local';
        break;
    default:
        die;
} 

return [
    'name' => env('APP_NAME', 'GGDocAPI'),
    'env' => env('APP_ENV', $env),
    'debug' => env('APP_DEBUG', $debug),
    'locale' => env('APP_LOCALE', 'es'),
];