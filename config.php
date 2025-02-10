<?php

// Проверяем, запущена ли уже сессия
if (session_status() === PHP_SESSION_NONE) {
    // Устанавливаем параметры сессии только если она еще не запущена
    ini_set('session.gc_maxlifetime', 864000);  // 10 дней
    session_set_cookie_params(864000); // Устанавливаем срок действия cookie
    session_start(); // Запускаем сессию
}

// Настройки базы данных
$site_url = "https://lnk.monster";
$servername = "4";
$username = "4";
$password = "4";
$dbname = "4";

$logging_enabled = true;
$default_long_url_image_url = "/img/no-meta-img.png";

// Подключение к базе данных
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
