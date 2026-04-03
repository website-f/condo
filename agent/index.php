<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$agentPrefix = '/agent';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($requestPath === $agentPrefix) {
    $query = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== ''
        ? '?'.$_SERVER['QUERY_STRING']
        : '';

    header('Location: '.$agentPrefix.'/'.$query, true, 301);
    exit;
}

if ($requestPath === $agentPrefix || str_starts_with($requestPath, $agentPrefix.'/')) {
    $_SERVER['SCRIPT_NAME'] = $agentPrefix.'/index.php';
    $_SERVER['PHP_SELF'] = $agentPrefix.'/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../_agent/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../_agent/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../_agent/bootstrap/app.php';

$app->handleRequest(Request::capture());
