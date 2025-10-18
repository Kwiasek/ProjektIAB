<?php

function routeRequest($uri) {
    switch ($uri) {
        case '/':
        case '/home':
            require_once __DIR__ . "/../views/facilities/list.php";
            break;

        case '/login':
            require_once __DIR__ . "/../views/auth/login.php";
            break;

        case '/register':
            require_once __DIR__ . "/../views/register.php";
            break;

        case '/logout':
            require_once __DIR__ . "../src/controllers/UserController.php";
            $controller = new UserController();
            $controller->logout();
            break;

        case '/facilities/add':
            require_once __DIR__ . '/../views/facilities/add.php';
            break;

        // Enddpointy do wysyłania formularzy
        case '/auth/login':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                require_once __DIR__ . "/../src/controllers/UserController.php";
                $controller = new UserController();
                $controller->login($_POST);
            }
            break;

        case '/auth/register':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                require_once __DIR__ . "/../src/controllers/UserController.php";
                $controller = new UserController();
                $controller->register($_POST);
            }
            break;

        default:
            http_response_code(404);
            echo '<h1 class="underline">404 - Strona nie została znaleziona</h1>';
            break;
    }
}

