<?php
session_start();
require_once 'config.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    if (isset($_POST['login'])) {
        $email = trim($_POST['login_email']);
        $password = trim($_POST['login_password']);

        if (empty($email) || empty($password)) {
            echo '<div class="alert alert-danger">Пожалуйста, заполните все поля.</div>';
        } else {
            // Подготовка запроса для логина
            $stmt = $conn->prepare("SELECT id, role, password, is_active FROM users WHERE email = ?");
            if ($stmt === false) {
                die("Ошибка подготовки запроса: " . $conn->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($user_id, $user_role, $hashed_password, $is_active);
            $stmt->fetch();

            if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
                if ($is_active == 1) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_role'] = $user_role;
                    echo '<div class="alert alert-success">Вход успешен! Перенаправление...</div>';
                    echo '<script>setTimeout(function() { window.location.href = "dashboard.php"; }, 2000);</script>';
                } else {
                    echo '<div class="alert alert-danger">Ваш аккаунт не активирован. Пожалуйста, проверьте вашу почту для подтверждения.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Неверная электронная почта или пароль</div>';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['register'])) {
        $email = trim($_POST['register_email']);
        $password = trim($_POST['register_password']);

        if (empty($email) || empty($password)) {
            echo '<div class="alert alert-danger">Пожалуйста, заполните все поля.</div>';
        } else {
            // Проверка существования пользователя
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if ($stmt === false) {
                die("Ошибка подготовки запроса: " . $conn->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo '<div class="alert alert-danger">Этот адрес электронной почты уже зарегистрирован.</div>';
            } else {
                // Генерация токена для подтверждения
                $token = bin2hex(random_bytes(16));

                // Хеширование пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Вставка нового пользователя с токеном и статусом неактивного
                $stmt = $conn->prepare("INSERT INTO users (email, password, token, is_active) VALUES (?, ?, ?, 0)");
                if ($stmt === false) {
                    die("Ошибка подготовки запроса: " . $conn->error);
                }

                $stmt->bind_param("sss", $email, $hashed_password, $token);

                if ($stmt->execute()) {
                    // Отправка письма с подтверждением
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.yandex.ru';          // SMTP сервер Yandex
                        $mail->SMTPAuth = true;
                        $mail->Username = 'dsgn@yrockets.com';   // Ваша почта Yandex
						$mail->Password = 'xsxqepvkckilcnji';  // Пароль от почты или пароль для приложений
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
                        $mail->Port = 465;
                        $mail->setFrom('dsgn@yrockets.com', 'ltl.link');
                        $mail->addAddress($email);
						
                        // Контент письма
                        $mail->isHTML(true);
                        $mail->Subject = 'Activation link';
                        $mail->Body = "To activate your account,:
                                       <a href='http://lnk.monster/confirm.php?email=$email&token=$token'>click here</a>";

                        $mail->send();
                        echo '<div class="alert alert-success">Регистрация успешна! Пожалуйста, подтвердите вашу почту.</div>';
                    } catch (Exception $e) {
                        echo "Ошибка отправки письма: {$mail->ErrorInfo}";
                    }
                } else {
                    echo '<div class="alert alert-danger">Ошибка регистрации: ' . $stmt->error . '</div>';
                }
            }
            $stmt->close();
        }
    }

    $conn->close();
} else {
    header("Location: login.php");
    exit();
}