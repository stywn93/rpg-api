<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'ratelimiter'], function($routes){
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/login', 'AuthController::login');

});
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'jwt:admin'], function ($routes) {
    $routes->resource('users', ['controller' => 'UserController']);
    $routes->patch('users/(:num)/password', 'UserController::updatePassword/$1');
    $routes->post('users/(:num)/activate/', 'UserController::activate/$1');
    $routes->post('users/(:num)/suspend', 'UserController::suspend/$1');

    $routes->resource('patients', ['controller' => 'PatientController', 'except' => ['show', 'edit']]);
    $routes->get('patients/(:num)', 'PatientController::show/$1'); //replace karen wildcard (:any) akan menghalangi get by parent
    $routes->get('patients/parent/(:num)', 'PatientController::showByParent/$1');

    $routes->resource('schedules', ['controller' => 'ScheduleController']);
    $routes->resource('servicetypes', ['controller' => 'ServiceTypeController']);
    $routes->get('queues/date/(:segment)', 'QueueController::filterByDate/$1');
    $routes->resource('queues', ['controller' => 'QueueController']);
    $routes->resource('queuelogs', ['controller' => 'QueueLogController']);
    $routes->get('growthrecords/patient/(:num)', 'GrowthRecordController::showByPatient/$1');
    $routes->resource('growthrecords', ['controller' => 'GrowthRecordController']);

    // new route for v_patients view
    $routes->get('patients/with-parents', 'PatientController::listWithParents');
});

$routes->set404Override('\App\Controllers\Api\V1\ErrorController::notFound');
