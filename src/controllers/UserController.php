<?php

require_once __DIR__ . "/../config/db.php";

class UserController {
    public function login($data) {
        global $pdo;

        $login = trim($data['login']);
        $password = trim($data['password']);

        if (empty($login) || empty($password)) {
            $_SESSION['error'] = "Wypełnij wszystkie pola";
            header("location: /login");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Nieprawidłowy login lub hasło";
            header("location: /login");
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Nieprawidłowy login lub hasło.";
            header('Location: /login');
            exit;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'login' => $user['login'],
            'role' => $user['role']
        ];

        header('location: /facilities/list');
        exit;
    }

    public function logout() {
        session_destroy();
        header("location: /login");
        exit;
    }
}