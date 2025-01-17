<?php
require_once 'config.php';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Подключение к базе данных
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Поиск пользователя по email и токену
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND token = ? AND is_active = 0");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Активируем пользователя
        $stmt = $conn->prepare("UPDATE users SET is_active = 1, token = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            echo "Account has been successfully activated.";
        } else {
            echo "Ошибка активации аккаунта.";
        }
    } else {
        echo "Неверная ссылка для активации.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Неверный запрос.";
}