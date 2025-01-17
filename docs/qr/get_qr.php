<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config.php';

// Проверка подключения к базе данных
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed: " . $conn->connect_error));
    exit();
}

$userId = isset($_GET['userId']) ? $conn->real_escape_string($_GET['userId']) : '';

if (empty($userId)) {
    http_response_code(400);
    echo json_encode(array("message" => "User ID is required."));
    exit();
}

$query = "SELECT * FROM qr_codes WHERE user_id = '$userId' ORDER BY created_at DESC";
$result = $conn->query($query);

if ($result === false) {
    http_response_code(500);
    echo json_encode(array("message" => "Query failed: " . $conn->error));
    exit();
}

if ($result->num_rows > 0) {
    $qr_codes = array();
    while ($row = $result->fetch_assoc()) {
        array_push($qr_codes, $row);
    }
    http_response_code(200);
    echo json_encode($qr_codes);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No QR codes found."));
}

$conn->close();
?>