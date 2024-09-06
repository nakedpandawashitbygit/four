<?php
session_start();
require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Проверка, авторизован ли пользователь
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // Перенаправление на дашборд
    exit();
}

$error_message = ""; // Переменная для хранения сообщения об ошибке
$success_message = ""; // Переменная для хранения сообщения об успешной регистрации

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Проверка на существование имени пользователя
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $error_message = "Ошибка: имя пользователя '$username' занято."; // Устанавливаем сообщение об ошибке
    } else {
        // Вставка нового пользователя в базу данных
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        
        if ($stmt->execute()) {
            $success_message = "Регистрация прошла успешно. Теперь вы можете <a href='login.php'>войти</a>."; // Устанавливаем сообщение об успешной регистрации
        } else {
            echo "Ошибка: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <style>
        .error { color: red; } /* Стиль для сообщения об ошибке */
        .success { color: green; } /* Стиль для сообщения об успешной регистрации */
    </style>
</head>
<body>
    <h1>Регистрация</h1>
    <?php if ($error_message): ?>
        <p class="error"><?php echo $error_message; ?></p> <!-- Отображаем сообщение об ошибке -->
    <?php elseif ($success_message): ?>
        <p class="success"><?php echo $success_message; ?></p> <!-- Отображаем сообщение об успешной регистрации -->
    <?php endif; ?>
    <form method="POST" action="registration.php">
        <input type="text" name="username" placeholder="Имя пользователя" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p><a href="login.php">Уже есть аккаунт? Войти</a></p>
</body>
</html>
