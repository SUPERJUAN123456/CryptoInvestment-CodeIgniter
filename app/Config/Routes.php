<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//Define qué URLs están disponibles en la app.
//Conecta cada URL a un método del controlador Crypto.
//Diferencia claramente entre peticiones GET y POST.
//Es indispensable para que CodeIgniter sepa cómo responder a cada solicitud.

$routes->get('/', 'Crypto::index'); // Página principal (vista)
$routes->get('data', 'Crypto::data'); // API de criptomonedas
$routes->get('historical-data', 'Crypto::historicalData'); // Datos históricos
$routes->post('favorites', 'Crypto::favorites'); // Marcar / desmarcar favoritos
