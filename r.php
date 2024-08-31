<?php
require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Получаем длинную ссылку по ID
    $stmt = $conn->prepare("SELECT long_url FROM four WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($long_url);
    $stmt->fetch();
    
    if ($stmt->num_rows > 0) {
        header("Location: " . $long_url);
        exit();
    } else {
        echo "Короткая ссылка не найдена.";
    }
    
    $stmt->close();
} else {
    echo "ID не указан.";
}

$conn->close();
?>