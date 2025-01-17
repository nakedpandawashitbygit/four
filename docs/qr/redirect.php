<?php
include_once 'config.php';

if (isset($_GET['id'])) {
    $qr_id = $_GET['id'];

    // Получаем оригинальный URL и увеличиваем счетчик переходов
    $stmt = $conn->prepare("SELECT qr_image_data FROM qr_codes WHERE id = ?");
    $stmt->bind_param("i", $qr_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $original_url = $row['qr_image_data'];

        // Увеличиваем счетчик переходов
        $updateStmt = $conn->prepare("UPDATE qr_codes SET visit_count = visit_count + 1 WHERE id = ?");
        $updateStmt->bind_param("i", $qr_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Перенаправляем пользователя на оригинальный URL
        header("Location: $original_url");
        exit();
    } else {
        echo "QR-код не найден";
    }

    $stmt->close();
} else {
    echo "Некорректный запрос";
}

$conn->close();
?>