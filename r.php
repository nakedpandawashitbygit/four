<?php
require_once 'config.php';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if (isset($_GET['id']) || isset($_GET['url'])) {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT long_url, password FROM four WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else if (isset($_GET['url'])) {
        $short_url = $_GET['url'];
        $stmt = $conn->prepare("SELECT long_url, password FROM four WHERE short_url = ?");
        $stmt->bind_param("s", $short_url);
    }

    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($long_url, $link_password);
    $stmt->fetch();
    
    if ($stmt->num_rows > 0) {
        if ($link_password) {
            // Если есть пароль, запрашиваем его у пользователя
            if (isset($_POST['password'])) {
                $entered_password = $_POST['password'];
                if ($entered_password === $link_password) {
                    // Пароль верный, перенаправляем на длинную ссылку
                    header("Location: " . $long_url);
                    exit();
                } else {
                    echo "Неверный пароль.";
                }
            } else {
                // Выводим форму для ввода пароля
                echo "<form method='POST'>";
                echo "Введите пароль для доступа к ссылке:<br>";
                echo "<input type='password' name='password'><br>";
                echo "<button type='submit'>Подтвердить</button>";
                echo "</form>";
                exit();
            }
        } else {
            // Если пароля нет, перенаправляем сразу на длинную ссылку
            header("Location: " . $long_url);
            exit();
        }
    } else {
        echo "Ссылка не найдена.";
    }
    
    $stmt->close();
} else {
    echo "ID или URL не указан.";
}

$conn->close();
?>
