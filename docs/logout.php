<?php
session_start();
session_unset(); // Удаляет все переменные сессии
session_destroy(); // Уничтожает сессию
header("Location: login.php"); // Перенаправление на страницу входа
exit();
?>