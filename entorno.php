<?php 
/**
 * entorno.php: Archivo ejemplo para variables de entorno de GDocAPI
 * @author Carlos Ahumada Vidal
 */

// Producción: 1, Develop: 0
	define("PRO", "1");


// CORS
	define("ORIGENES_PERMITIDOS", array(
		"https://server.es",
	));

// JWT 

	define("SECRET_KEY", "******************************");
	define("JWT_ACCESS_EXP", "900");
	define("JWT_REFRESH_EXP", "21600");


// CREDENCIALES CONSUMIDORES

	define("USER_PASSWD", array(
		["cliente", "password", ["ROLE_ADMIN_CLINICO", "ROLE_ADMIN_RRHH"]],
	));



// CEDENCIALES ALFRESCO
	
	define("ALFRESCO_USER", "user");
	define("ALFRESCO_PASSW", "passwd");


// BUSQUEDA - NUMERO RESULTADOS MAXIMO: 
// Resultados máximos por consulta
	define("BUSQUEDA_MAX", "500");


// TIEMPO CACHE NUEVOS NODOS (Redis)
// Tiempo que se conservan en caché los id de los nodos-folder creados (par evitar lag de indexación)

	define("REDIS_TIEMPO_CACHE", 3600);


// TIEMPO ESPERA ENTRE INTENTOS DE DESCARGA ZIP ALFRESCO Y TIEMPO MAXIMO
// Tiempo entre iteraciones para intentar descargar el ZIP que está generando Alfresco y tiempo máximo

	define("ZIP_TIEMPO_ESPERA", "5"); 
	define("ZIP_TIEMPO_MAX", "90");  
