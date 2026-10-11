<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Security Awareness Hub - Backend MVC
|--------------------------------------------------------------------------
| Punto de carga central. Inicializa la sesión, define la cabecera JSON,
| registra un autoloader para Core, Models y Controllers y carga la
| configuración de base de datos.
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!headers_sent()) {
    header("Content-Type: application/json; charset=UTF-8");
}

// En producción nunca se muestran errores al cliente (romperían el JSON).
error_reporting(E_ALL);
ini_set("display_errors", "0");
ini_set("log_errors", "1");

// Conexión a base de datos (define la clase Database).
require_once __DIR__ . "/Config/database.php";

// Autoloader simple: Core, Models y Controllers viven en el namespace global.
spl_autoload_register(static function (string $class): void {
    $dirs = [
        __DIR__ . "/Core/",
        __DIR__ . "/Models/",
        __DIR__ . "/Controllers/",
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . ".php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
