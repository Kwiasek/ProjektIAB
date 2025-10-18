<?php

session_start();

require_once __DIR__ . "/../src/config/db.php"; // Połączenie z bazą
require_once __DIR__ . "/../src/utils/helpers.php"; // Pomocnicze funkcje
require_once __DIR__ . "/../routes/web.php"; // Router aplikacji

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

routeRequest($request_uri);