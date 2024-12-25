<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Confirmation</title>
    <!-- Подключаем Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Скрипт для перенаправления через 3 секунды
        function redirectToLogin() {
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000); // 3000 миллисекунд = 3 секунды
        }
    </script>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <?php
                    require_once 'config.php';
                    
                    // Проверка активации аккаунта
                    if (isset($_GET['email']) && isset($_GET['token'])) {
                        $email = $_GET['email'];
                        $token = $_GET['token'];

                        // Подключение к базе данных
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        if ($conn->connect_error) {
                            die("<div class='alert alert-danger'>Ошибка подключения: " . $conn->connect_error . "</div>");
                        }

                        // Поиск пользователя по email и токену
                        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND token = ? AND is_active = 0");
                        $stmt->bind_param("ss", $email, $token);
                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows > 0) {
                            // Активируем пользователя
                            $stmt = $conn->prepare("UPDATE users SET is_active = 1, token = NULL WHERE email = ?");
                            $stmt->bind_param("s", $email);
                            if ($stmt->execute()) {
                                echo "<div class='alert alert-success'>Account has been successfully activated.</div>";
                                // Запускаем перенаправление на login.php через 3 секунды
                                echo "<script>redirectToLogin();</script>";
                            } else {
                                echo "<div class='alert alert-danger'>Ошибка активации аккаунта.</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Неверная ссылка для активации.</div>";
                        }

                        $stmt->close();
                        $conn->close();
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Подключаем Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>