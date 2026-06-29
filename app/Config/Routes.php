<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Auth::login');
$routes->get('q/(:segment)', 'Scan::location/$1');
$routes->get('display/(:num)/(:segment)/token', 'Display::token/$1/$2');
$routes->get('display/(:num)/(:segment)', 'Display::screen/$1/$2');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('punch', 'Punch::index');
    $routes->get('history', 'History::index');
    $routes->post('attendance/punch', 'Attendance::punch');
    $routes->get('requests', 'Requests::index');
    $routes->post('requests/leave', 'Requests::createLeave');
    $routes->post('requests/advance', 'Requests::createAdvance');
    $routes->get('notifications', 'Notifications::index');
});

$routes->group('admin', ['filter' => 'admin', 'namespace' => 'App\Controllers\Admin'], static function (RouteCollection $routes) {
    $routes->get('', 'Home::index');

    $routes->get('employees', 'Employees::index');
    $routes->get('employees/new', 'Employees::new');
    $routes->post('employees', 'Employees::create');
    $routes->post('employees/bulk', 'Employees::bulk');
    $routes->get('employees/(:num)/edit', 'Employees::edit/$1');
    $routes->post('employees/(:num)', 'Employees::update/$1');
    $routes->get('employees/(:num)', 'Employees::show/$1');

    $routes->get('departments', 'Departments::index');
    $routes->get('departments/new', 'Departments::new');
    $routes->post('departments', 'Departments::create');
    $routes->get('departments/(:num)/edit', 'Departments::edit/$1');
    $routes->post('departments/(:num)', 'Departments::update/$1');
    $routes->post('departments/(:num)/delete', 'Departments::delete/$1');

    $routes->get('positions', 'Positions::index');
    $routes->get('positions/new', 'Positions::new');
    $routes->post('positions', 'Positions::create');
    $routes->get('positions/(:num)/edit', 'Positions::edit/$1');
    $routes->post('positions/(:num)', 'Positions::update/$1');
    $routes->post('positions/(:num)/delete', 'Positions::delete/$1');

    $routes->get('shifts', 'Shifts::index');
    $routes->get('shifts/new', 'Shifts::new');
    $routes->post('shifts', 'Shifts::create');
    $routes->get('shifts/(:num)/edit', 'Shifts::edit/$1');
    $routes->post('shifts/(:num)', 'Shifts::update/$1');
    $routes->post('shifts/(:num)/delete', 'Shifts::delete/$1');
    $routes->get('shift-schedule', 'ShiftSchedule::index');
    $routes->post('shift-schedule', 'ShiftSchedule::save');

    $routes->get('locations', 'Locations::index');
    $routes->get('locations/new', 'Locations::new');
    $routes->post('locations', 'Locations::create');
    $routes->get('locations/(:num)/edit', 'Locations::edit/$1');
    $routes->post('locations/(:num)', 'Locations::update/$1');
    $routes->post('locations/(:num)/delete', 'Locations::delete/$1');
    $routes->get('locations/(:num)/qr', 'Locations::qr/$1');
    $routes->get('locations/(:num)/token', 'Locations::token/$1');

    $routes->get('suspicious', 'Suspicious::index');
    $routes->get('attendance', 'Attendance::index');
    $routes->get('attendance/new', 'Attendance::new');
    $routes->post('attendance', 'Attendance::create');
    $routes->get('attendance/(:num)/edit', 'Attendance::edit/$1');
    $routes->post('attendance/(:num)', 'Attendance::update/$1');
    $routes->post('attendance/(:num)/delete', 'Attendance::delete/$1');

    // Talepler
    $routes->get('requests', 'Requests::index');
    $routes->post('requests/leave/(:num)/approve', 'Requests::approveLeave/$1');
    $routes->post('requests/leave/(:num)/reject', 'Requests::rejectLeave/$1');
    $routes->post('requests/advance/(:num)/approve', 'Requests::approveAdvance/$1');
    $routes->post('requests/advance/(:num)/reject', 'Requests::rejectAdvance/$1');

    $routes->get('payroll', 'Payroll::index');
    $routes->get('payroll/(:num)', 'Payroll::show/$1');
    $routes->post('payroll/(:num)/advance', 'Payroll::addAdvance/$1');
    $routes->post('payroll/(:num)/advance/(:num)/delete', 'Payroll::deleteAdvance/$1/$2');

    $routes->get('reports', 'Reports::index');
    $routes->get('reports/export', 'Reports::export');
    $routes->get('reports/print', 'Reports::printView');
    $routes->get('settings', 'Settings::index');
    $routes->post('settings', 'Settings::save');
});
