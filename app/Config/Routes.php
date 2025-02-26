<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/lacak_bus', 'Home::lacak_bus');
$routes->get('/polyline', 'Home::getPolyline');
