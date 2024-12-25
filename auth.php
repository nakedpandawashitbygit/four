<?php

//попытка увеличить продолжительность сессии
ini_set('session.gc_maxlifetime', 864000);  // 24 часа
session_set_cookie_params(864000); // срок действия cookie сессии

session_start();


if (isset($_COOKIE['auth_token'])) {
    $auth_token = $_COOKIE['auth_token'];
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, role FROM users WHERE auth_token = ?");
    $stmt->bind_param("s", $auth_token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $user_role);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = $user_role;
        header("Location: dashboard.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}


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

    // Обработка логина
    if (isset($_POST['login'])) {
        $email = trim($_POST['login_email']);
        $password = trim($_POST['login_password']);

        if (empty($email) || empty($password)) {
            echo '<div class="alert alert-danger">Пожалуйста, заполните все поля.</div>';
        } else {
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
        
                // Генерация токена
                $auth_token = bin2hex(random_bytes(32));
        
                // Сохранение токена в базе данных
                $stmt_update = $conn->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
                $stmt_update->bind_param("si", $auth_token, $user_id);
                if ($stmt_update->execute()) {
                    // Установка токена в cookie
                    setcookie("auth_token", $auth_token, time() + 86400, "/", "", true, true); // 24 часа
                }
                $stmt_update->close();
        
                echo '<div class="alert alert-success">Вход успешен! Перенаправление...</div>';
                echo '<script>setTimeout(function() { window.location.href = "dashboard"; }, 2000);</script>';
            } else {
                echo '<div class="alert alert-danger">Ваш аккаунт не активирован. Пожалуйста, проверьте вашу почту для подтверждения.</div>';
            }
            } else {
                echo '<div class="alert alert-danger">Неверная электронная почта или пароль</div>';
            }
            $stmt->close();
        }
    }

    // Обработка регистрации
    elseif (isset($_POST['register'])) {
        $email = trim($_POST['register_email']);
        $password = trim($_POST['register_password']);

        if (empty($email) || empty($password)) {
            echo '<div class="alert alert-danger">Пожалуйста, заполните все поля.</div>';
        } else {
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
                $token = bin2hex(random_bytes(16));
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (email, password, token, is_active) VALUES (?, ?, ?, 0)");
                if ($stmt === false) {
                    die("Ошибка подготовки запроса: " . $conn->error);
                }

                $stmt->bind_param("sss", $email, $hashed_password, $token);

                if ($stmt->execute()) {
                    sendActivationEmail($email, $token);
                    echo '<div class="alert alert-success">Регистрация успешна! Пожалуйста, подтвердите вашу почту.</div>';
                } else {
                    echo '<div class="alert alert-danger">Ошибка регистрации: ' . $stmt->error . '</div>';
                }
            }
            $stmt->close();
        }
    }

    // Обработка повторной отправки письма подтверждения
    elseif (isset($_POST['resend'])) {
        $email = trim($_POST['email']);
        
        // Проверка существования пользователя и его токена
        $stmt = $conn->prepare("SELECT token FROM users WHERE email = ? AND is_active = 0");
        if ($stmt === false) {
            die("Ошибка подготовки запроса: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($token);
        $stmt->fetch();

        if ($stmt->num_rows > 0) {
            sendActivationEmail($email, $token);
            echo '<div class="alert alert-success">Письмо отправлено повторно!</div>';
        } else {
            echo '<div class="alert alert-danger">Не удалось отправить письмо. Возможно, ваш аккаунт уже активирован или адрес электронной почты неверен.</div>';
        }

        $stmt->close();
    }

    $conn->close();
} else {
    header("Location: login.php");
    exit();
}

// Функция для отправки письма с подтверждением
function sendActivationEmail($email, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.nic.ru';
        $mail->SMTPAuth = true;
        $mail->Username = 'welcome@lnk.monster';
        $mail->Password = 'pop!io4=Artem';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom('welcome@lnk.monster', 'ltl.link');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Activation link';
        $mail->Body = "To activate your account, please <a href='http://lnk.monster/confirm.php?email=$email&token=$token'>click here</a>";

        $mail->send();
    } catch (Exception $e) {
        echo "Ошибка отправки письма: {$mail->ErrorInfo}";
    }
}