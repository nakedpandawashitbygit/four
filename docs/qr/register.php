<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config.php'; // Подключаем файл с конфигурацией базы данных

// Получаем данные из POST-запроса
$data = json_decode(file_get_contents("php://input"));

// Проверяем, что данные были успешно декодированы
if ($data === null) {
    http_response_code(400); // Плохой запрос
    echo json_encode(array("message" => "Invalid JSON"));
    exit();
}

// Проверяем, что имя пользователя и пароль не пустые
if (!empty($data->username) && !empty($data->password)) {
    // Подготовка имени пользователя
    $username = $data->username;

    // Проверяем, существует ли пользователь
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt === false) {
        http_response_code(500); // Ошибка сервера
        echo json_encode(array("message" => "Ошибка подготовки запроса: " . $conn->error));
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(409); // Конфликт - пользователь уже существует
        echo json_encode(array("message" => "Пользователь уже существует"));
    } else {
        // Хешируем пароль
        $password = password_hash($data->password, PASSWORD_DEFAULT);

        // Вставляем нового пользователя в базу данных
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt === false) {
            http_response_code(500); // Ошибка сервера
            echo json_encode(array("message" => "Ошибка подготовки запроса: " . $conn->error));
            exit();
        }

        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            http_response_code(201); // Создано
            echo json_encode(array("message" => "Регистрация успешна"));
        } else {
            http_response_code(500); // Ошибка сервера
            echo json_encode(array("message" => "Ошибка регистрации: " . $stmt->error));
        }
    }

    // Закрываем запрос
    $stmt->close();
} else {
    http_response_code(400); // Плохой запрос
    echo json_encode(array("message" => "Username and password are required"));
}

$conn->close(); // Закрываем соединение с базой данных
?>