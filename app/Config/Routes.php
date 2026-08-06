<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Full route map for RMS_Backend_Architecture_and_Prompts.md v2.0, Section 6.
 * jwtauth / role / throttle refer to the aliases registered in Config/Filters.php.
 */

$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], static function ($routes) {

    // Public -- no auth required
    $routes->get('docs', static function() {
        return redirect()->to('/docs/index.html');
    });
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/refresh', 'AuthController::refresh');
    $routes->get('health', 'HealthController::index');

    // Everything below requires a valid access token
    $routes->group('', ['filter' => 'jwtauth'], static function ($routes) {

        $routes->post('auth/logout', 'AuthController::logout');
        $routes->get('auth/me', 'AuthController::me');

        // Staff / user management -- admin only (Section 6.2)
        $routes->group('users', ['filter' => 'role:admin'], static function ($routes) {
            $routes->get('', 'UserController::index');
            $routes->get('(:num)', 'UserController::show/$1');
            $routes->post('', 'UserController::create');
            $routes->put('(:num)', 'UserController::update/$1');
            $routes->patch('(:num)/status', 'UserController::setStatus/$1');
            $routes->post('(:num)/reset-password', 'UserController::resetPassword/$1');
        });

        // Dashboard -- admin + cashier (Section 6.3)
        $routes->get('dashboard/summary', 'DashboardController::summary');

        // Menu categories -- reads open to any authenticated role, writes admin-only (Section 6.4)
        $routes->get('menu-categories', 'MenuCategoryController::index');
        $routes->get('menu-categories/(:num)', 'MenuCategoryController::show/$1');
        $routes->get('menu-categories/(:num)/items', 'MenuItemController::byCategory/$1');
        $routes->group('menu-categories', ['filter' => 'role:admin'], static function ($routes) {
            $routes->post('', 'MenuCategoryController::create');
            $routes->put('(:num)', 'MenuCategoryController::update/$1');
            $routes->delete('(:num)', 'MenuCategoryController::delete/$1');
        });

        // Menu items (Section 6.5)
        $routes->get('menu-items', 'MenuItemController::index');
        $routes->get('menu-items/(:num)', 'MenuItemController::show/$1');
        $routes->group('menu-items', ['filter' => 'role:admin'], static function ($routes) {
            $routes->post('', 'MenuItemController::create');
            $routes->put('(:num)', 'MenuItemController::update/$1');
            $routes->delete('(:num)', 'MenuItemController::delete/$1');
        });

        // Restaurant tables (Section 6.6)
        $routes->get('tables', 'TableController::index');
        $routes->get('tables/(:num)', 'TableController::show/$1');
        $routes->group('tables', ['filter' => 'role:admin,cashier'], static function ($routes) {
            $routes->patch('(:num)/status', 'TableController::updateStatus/$1');
        });
        $routes->group('tables', ['filter' => 'role:admin'], static function ($routes) {
            $routes->post('', 'TableController::create');
            $routes->put('(:num)', 'TableController::update/$1');
            $routes->delete('(:num)', 'TableController::delete/$1');
        });

        // Orders + Billing -- cashier + admin (Section 6.7, 6.8)
        $routes->group('orders', ['filter' => 'role:admin,cashier'], static function ($routes) {
            $routes->get('', 'OrderController::index');
            $routes->get('(:num)', 'OrderController::show/$1');
            $routes->post('', 'OrderController::create');
            $routes->post('(:num)/items', 'OrderController::addItem/$1');
            $routes->put('(:num)/items/(:num)', 'OrderController::updateItem/$1/$2');
            $routes->delete('(:num)/items/(:num)', 'OrderController::removeItem/$1/$2');
            $routes->post('(:num)/complete', 'OrderController::complete/$1');
            $routes->post('(:num)/cancel', 'OrderController::cancel/$1');
            $routes->post('(:num)/bill', 'BillingController::generate/$1');
        });
        $routes->group('bills', ['filter' => 'role:admin,cashier'], static function ($routes) {
            $routes->get('(:num)', 'BillingController::show/$1');
            $routes->post('(:num)/pay', 'BillingController::pay/$1');
            $routes->get('(:num)/receipt', 'BillingController::receipt/$1');
        });

        // Sales reports -- admin only (Section 6.9)
        $routes->group('reports', ['filter' => 'role:admin'], static function ($routes) {
            $routes->get('daily', 'ReportController::daily');
            $routes->get('monthly', 'ReportController::monthly');
            $routes->get('daily/print', 'ReportController::dailyPrint');
            $routes->get('monthly/print', 'ReportController::monthlyPrint');
        });
    });
});
// ========================================================================
// FRONTEND UI ROUTES (Serving the Bootstrap 5 Views)
// ========================================================================

// Default root loads the login page
$routes->get('/', static function() {
    return view('login');
});

// Explicit login route
$routes->get('login', static function() {
    return view('login');
});

// Dashboard and main app routes
$routes->get('dashboard', static function() {
    return view('dashboard');
});

$routes->get('menu', static function() {
    return view('menu');
});

$routes->get('orders', static function() {
    return view('order');
});

$routes->get('order', static function() {
    return view('order');
});

$routes->get('reports', static function() {
    return view('reports');
});

$routes->get('tables', static function() {
    return view('tables');
});

$routes->get('users', static function() {
    return view('users');
});

$routes->get('billing', static function() {
    return view('billing');
});