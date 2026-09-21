<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

// @var RouteCollection $routes
$routes->get('/', 'Home::index');
$routes->get('home/edit', 'Home::edit');
$routes->post('home/update', 'Home::update');