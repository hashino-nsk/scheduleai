<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('login/google', 'Auth::google');
$routes->get('login/google/callback', 'Auth::callback');
