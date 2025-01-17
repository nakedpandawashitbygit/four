<?php
session_start(); // Начинаем сессию

// Проверяем, если пользователь хочет выйти
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Удаляем все переменные сессии
    $_SESSION = array();

    // Если вы хотите уничтожить сессию, также удалите куки
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }

    // Уничтожаем сессию
    session_destroy();

    // Перенаправляем на страницу входа
    header("Location: login.html");
    exit();
}

// Проверяем, авторизован ли пользователь
if (isset($_SESSION['user_id'])) {
    // Пользователь авторизован, можно продолжать
    $username = $_SESSION['username'];
} else {
    // Пользователь не авторизован, перенаправляем на страницу входа
    header("Location: login.html");
    exit();
}
?>