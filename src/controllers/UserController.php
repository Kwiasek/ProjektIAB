<?php

use JetBrains\PhpStorm\NoReturn;

require_once __DIR__ . "/../config/db.php";

class UserController {
    #[NoReturn]
    public function login($data): void
    {
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

        // Transfer liked facilities from session to database
        if (isset($_SESSION['liked_facilities']) && is_array($_SESSION['liked_facilities'])) {
            require_once __DIR__ . "/../models/Facility.php";
            $facilityModel = new Facility();
            foreach ($_SESSION['liked_facilities'] as $facilityId) {
                $facilityModel->likeFacility($user['id'], $facilityId);
            }
            unset($_SESSION['liked_facilities']);
            session_write_close();
        }

        header('location: /');
        exit;
    }

    #[NoReturn]
    public function register($data): void
    {
        global $pdo;

        $fname = trim($data['fname']);
        $lname = trim($data['lname']);
        $login = trim($data['login']);
        $email = trim($data['email']);
        $phone = trim($data['phone']);
        $password = trim($data['password']);
        $password_confirm = trim($data['password_confirm']);
        $owner = isset($data['owner']);

        echo($owner);

        if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($password) || empty($password_confirm)) {
            $_SESSION['error'] = "Wszystkie pola muszą być wypełnione";
            header("location: /register");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Podany e-mail jest niepoprawny";
            header("location: /register");
            exit;
        }

        if ($password !== $password_confirm) {
            $_SESSION['error'] = "Podane hasła muszą być identyczne";
            header("location: /register");
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = "Hasło musi składać się z conajmniej 6 znaków";
            header("location: /register");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Użytkownik o podanym loginie już istnieje";
            header("location: /register");
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $name = $fname . " " . $lname;
        $role = $owner ? "owner" : "user";

        $stmt = $pdo->prepare("INSERT INTO users (login, name, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        if(!$stmt->execute([$login, $name, $hashedPassword, $role])) {
            $_SESSION['error'] = "Wystąpił błąd podczas rejestracji";
            header("location: /register");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("INSERT INTO user_contacts (user_id, email, phone) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $email, $phone]);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'login' => $user['login'],
            'role' => $user['role']
        ];
        header('location: /');
        exit;
    }

    #[NoReturn]
    public function logout(): void
    {
        session_destroy();
        header("location: /login");
        exit;
    }
}