<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO news (title, content) VALUES (?, ?)");
    if (!$stmt) {
        die('Ошибка подготовки запроса: ' . $conn->error);
    }
    $stmt->bind_param("ss", $title, $content);
    $stmt->execute();
    $stmt->close();

    echo "<p>Новость добавлена!</p>";
}
?>


<form action="" method="POST">
    <input type="text" name="title" placeholder="Заголовок" required class="form-control mb-2">
    <textarea name="content" placeholder="Содержание" required class="form-control mb-2"></textarea>
    <button type="submit" class="btn btn-success">Добавить новость</button>
</form>
