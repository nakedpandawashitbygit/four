<?php
session_start();

header("Content-Type: application/json");

include_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(array("message" => "Unauthorized"));
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (isset($data->userId) && !empty($data->url)) {
    $user_id = $data->userId;
    $original_url = $data->url;
    $shortened_url = bin2hex(random_bytes(3)); // Генерируем случайную сокращенную ссылку

    $stmt = $conn->prepare("INSERT INTO shortened_urls (user_id, original_url, shortened_url) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $original_url, $shortened_url);

    if ($stmt->execute()) {
        echo json_encode(array("shortened_url" => $shortened_url));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Ошибка при сохранении сокращенной ссылки: " . $stmt->error));
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Некорректные данные"));
}

$conn->close();
?>