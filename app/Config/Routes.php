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
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'jwt'], function ($routes) {
    // $routes->resource('users', [
    //     'controller' => 'UserController'
    //     ]);
    $routes->get('users', 'UserController::index');
    $routes->get('users/(:num)', 'UserController::show/$1');
    $routes->post('users', 'UserController::create');
    $routes->patch('users/(:num)', 'UserController::update/$1');
    $routes->delete('users/(:num)', 'UserController::delete/$1');
    // $routes->patch('users/(:num)/password', 'UserController::updatePassword/$1');
    // $routes->post('users/(:num)/activate/', 'UserController::activate/$1');
    // $routes->post('users/(:num)/suspend', 'UserController::suspend/$1');


    $routes->resource('patients', ['controller' => 'PatientController', 'except' => ['new', 'show', 'edit']]);
    $routes->get('patients/(:num)', 'PatientController::show/$1');
    $routes->get('patients/parent/(:num)', 'PatientController::showByParent/$1');

    // $routes->resource('schedules', ['controller' => 'ScheduleController']);
    // $routes->resource('servicetypes', ['controller' => 'ServiceTypeController']);
    // $routes->get('queues/date/(:segment)', 'QueueController::filterByDate/$1');
    /* $routes->resource('queues', ['controller' => 'QueueController']); */
    // $routes->resource('queuelogs', ['controller' => 'QueueLogController']);
    // $routes->get('growthrecords/patient/(:num)', 'GrowthRecordController::showByPatient/$1');
    // $routes->resource('growthrecords', ['controller' => 'GrowthRecordController']);

    // new route for v_patients view
    // $routes->get('patients/with-parents', 'PatientController::listWithParents');
    // $routes->get('patients/with-parents/(:num)', 'PatientController::listWithParents/$1');

    // new route for v_queues
    $routes->get('queues/all', 'QueueController::listWithPatients');
    $routes->patch('queues/(:num)', 'QueueController::update/$1');

    $routes->resource('visits', ['controller' => 'VisitController', 'except' => ['new','show', 'edit']]);
    $routes->get('visits-by-parent', 'VisitController::indexByParent');
    $routes->get('visits/(:num)', 'VisitController::show/$1');
    $routes->resource('medical-services', ['controller' => 'MedicalServiceController', 'except' => ['show', 'edit']]);
    $routes->get('medical-services/(:num)', 'MedicalServiceController::show/$1');

    $routes->resource('visit-services', ['controller' => 'VisitServiceController', 'except' => ['new', 'show', 'edit', 'update']]);
    // tambahan route untuk diff and sync services
    $routes->put('visit-services/(:num)', 'VisitServiceController::updateServices/$1');
    $routes->get('visit-services/(:num)', 'VisitServiceController::show/$1');
    
});

$routes->set404Override('\App\Controllers\Api\V1\ErrorController::notFound');
