<?php

session_start();

define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once __DIR__ . '/../app/Core/autoload.php';

$routes = require __DIR__ . '/../routes/web.php';
(new App\Core\Router())->dispatch($routes);
