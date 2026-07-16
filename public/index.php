<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Local runs on PHP 8.5 while the app targets 8.1 — silence deprecation noise so
// it never leaks into page output. (display_errors must be off in prod anyway.)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
@ini_set('display_errors', '0');

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
