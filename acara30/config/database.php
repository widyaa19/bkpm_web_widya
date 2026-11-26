<?php

$readEnvironmentValue = static function (string $name): ?string {
    $value = getenv($name);
    if ($value !== false && $value !== '') {
        return $value;
    }

    foreach ([$_ENV, $_SERVER] as $source) {
        if (isset($source[$name]) && is_string($source[$name]) && $source[$name] !== '') {
            return $source[$name];
        }
    }

    return null;
};

$host = $readEnvironmentValue('DB_HOST');
$database = $readEnvironmentValue('DB_NAME');
$username = $readEnvironmentValue('DB_USER');
$password = $readEnvironmentValue('DB_PASSWORD');

return [
    'host' => $host ?? 'localhost',
    'dbname' => $database ?? 'si_akademik',
    'username' => $username ?? 'root',
    'password' => $password ?? '',
    'charset' => 'utf8mb4',
];