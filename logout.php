<?php
session_start();
require_once 'config.php';

// Удаление токена из базы данных
if (isset($_COOKIE['auth_token'])) {
    $auth_token = $_COOKIE['auth_token'];

    // Подключение к базе данных
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Удаление токена из базы
    $stmt = $conn->prepare("UPDATE users SET auth_token = NULL WHERE auth_token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $auth_token);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    // Удаление токена из cookie
    setcookie("auth_token", "", time() - 3600, "/", "", true, true);
}

// Очистка сессии
session_unset(); // Удаляет все переменные сессии
session_destroy(); // Уничтожает сессию

// Перенаправление на страницу входа
header("Location: login.php");
exit();
?>