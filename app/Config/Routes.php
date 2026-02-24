<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], function($routes){
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/login', 'AuthController::login');

});
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'jwt:admin'], function ($routes) {
    $routes->resource('users', ['controller' => 'UserController']);
    $routes->post('users/(:num)/activate/', 'UserController::activate/$1');
    $routes->post('users/(:num)/suspend', 'UserController::suspend/$1');
});

$routes->set404Override('\App\Controllers\Api\V1\ErrorController::notFound');