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
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'jwt:admin'], function($routes){
    $routes->resource('users', ['controller' => 'UserController']);
    $routes->patch('users/(:num)/activate', 'UserController::activate/$1');
    $routes->patch('users/(:num)/suspend', 'UserController::suspend/$1');
});