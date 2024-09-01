<?php
require_once 'config.php';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if (isset($_GET['id']) || isset($_GET['url'])) {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT long_url FROM four WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else if (isset($_GET['url'])) {
        $short_url = $_GET['url'];
        $stmt = $conn->prepare("SELECT long_url FROM four WHERE short_url = ?");
        $stmt->bind_param("s", $short_url);
    }

    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($long_url);
    $stmt->fetch();
    
    

    if ($stmt->num_rows > 0) {
        header("Location: " . $long_url);
        exit();
    } else {
        echo "Ссылка не найдена.";
    }
}

$conn->close();
?>
