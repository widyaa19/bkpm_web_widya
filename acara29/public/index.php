<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

session_start();

define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
$appConfig = require __DIR__ . '/../config/app.php';
define('APP_ENV', $appConfig['environment']);

require_once __DIR__ . '/../app/Core/autoload.php';

$routes = require __DIR__ . '/../routes/web.php';
(new App\Core\Router())->dispatch($routes);
