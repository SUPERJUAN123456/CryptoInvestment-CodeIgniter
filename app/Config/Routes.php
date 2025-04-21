<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Crypto::index'); // Página principal (vista)
$routes->get('data', 'Crypto::data'); // API de criptomonedas
$routes->get('historical-data', 'Crypto::historicalData'); // Datos históricos
$routes->post('favorites', 'Crypto::favorites'); // Marcar / desmarcar favoritos
