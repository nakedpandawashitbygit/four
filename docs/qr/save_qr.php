<?php
session_start(); // Начинаем сессию

header("Content-Type: application/json"); // Устанавливаем заголовок для JSON

include_once 'config.php'; // Подключаем файл с конфигурацией базы данных

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Доступ запрещен
    echo json_encode(array("message" => "Unauthorized"));
    exit();
}

// Получаем данные из POST-запроса
$data = json_decode(file_get_contents("php://input"));

// Проверяем, что данные были успешно декодированы
if ($data === null) {
    http_response_code(400); // Плохой запрос
    echo json_encode(array("message" => "Invalid JSON"));
    exit();
}

// Проверяем, что user_id и текст QR-кода не пустые
if (!empty($data->user_id) && !empty($data->text)) {
    $user_id = $data->user_id;
    $qr_code_data = $data->text; // текст QR-кода
    //$qr_image_data = $data->imageData; // изображение QR-кода

    // Подготовка SQL-запроса для сохранения QR-кода
    $stmt = $conn->prepare("INSERT INTO qr_codes (user_id, qr_code_data) VALUES (?, ?)");
    if ($stmt === false) {
        http_response_code(500); // Ошибка сервера
        echo json_encode(array("message" => "Ошибка подготовки запроса: " . $conn->error));
        exit();
    }

    // Привязываем параметры и выполняем запрос
    $stmt->bind_param("is", $user_id, $qr_code_data);
    if ($stmt->execute()) {
        echo json_encode(array("message" => "QR-код успешно сохранён"));
    } else {
        http_response_code(500); // Ошибка сервера
        echo json_encode(array("message" => "Ошибка при сохранении QR-кода: " . $stmt->error));
    }

    // Закрываем запрос
    $stmt->close();
} else {
    http_response_code(400); // Плохой запрос
    echo json_encode(array("message" => "user_id and QR code data are required"));
}

$conn->close(); // Закрываем соединение с базой данных
?>
