<?php
// site.php: configuración específica de cada SITE
// Carlos Ahumada Vidal
// Configurar CORS

// Cargamos la congiguración general
require '../config/config.php';

//cargamos la aplicación (Lumen)
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->run();

