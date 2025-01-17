<?php
session_start();
session_unset(); // Удаляем все переменные сессии
session_destroy(); // Уничтожаем сессию
http_response_code(200);
echo json_encode(array("message" => "Session cleared"));
?>