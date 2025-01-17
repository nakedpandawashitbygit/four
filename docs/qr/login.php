<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config.php'; // Подключаем файл с конфигурацией базы данных

$data = json_decode(file_get_contents("php://input")); // Получаем данные из POST-запроса

if (!empty($data->username) && !empty($data->password)) {
    $username = $conn->real_escape_string($data->username); // Экранируем имя пользователя

    // Проверяем, существует ли пользователь
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Получаем данные пользователя
        
        //var_dump($user); // Выводим данные для проверки
        
        // Проверяем пароль
        if (password_verify($data->password, $user['password']))
        {$_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username'];
            
            http_response_code(200); // Успех
            echo json_encode(array("message" => "Login successful", "user" => $user));}
            else
            {http_response_code(401); // Неверный пароль
            echo json_encode(array("message" => "Invalid password"));}
    } else {
        http_response_code(404); // Пользователь не найден
        echo json_encode(array("message" => "User not found"));
    }
} else {
    http_response_code(400); // Плохой запрос
    echo json_encode(array("message" => "Username and password are required"));
}

$conn->close(); // Закрываем соединение с базой данных
?>
