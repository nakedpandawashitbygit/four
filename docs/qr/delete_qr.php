<?php
session_start(); // Начинаем сессию

header("Content-Type: application/json"); // Устанавливаем заголовок для JSON

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Доступ запрещен
    echo json_encode(array("success" => false, "message" => "Unauthorized"));
    exit();
}

// Подключаем файл с конфигурацией базы данных
include_once 'config.php';

// Получаем данные из POST-запроса
$data = json_decode(file_get_contents("php://input"));

// Проверяем, что id был передан
if (isset($data->id)) {
    $id = $data->id;

    // Подготовка SQL-запроса для удаления QR-кода
    $stmt = $conn->prepare("DELETE FROM qr_codes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(array("success" => true));
    } else {
        echo json_encode(array("success" => false, "message" => "Ошибка при удалении QR-кода: " . $stmt->error));
    }

    // Закрываем запрос
    $stmt->close();
} else {
    echo json_encode(array("success" => false, "message" => "ID не указан"));
}

// Закрываем соединение с базой данных
$conn->close();
?>