<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Настройки PHPMailer
$mail = new PHPMailer(true);
try {
    // Настройки SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';          // SMTP сервер Yandex
    $mail->SMTPAuth = true;
    $mail->Username = 'dsgn@yrockets.com';   // Ваша почта Yandex
    $mail->Password = 'xsxqepvkckilcnji';  // Пароль от почты или пароль для приложений
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    $mail->Port = 465;                        // Порт для SSL

    // От кого отправляется письмо
    $mail->setFrom('dsgn@yrockets.com', 'ltl.link');
    
    // Кому отправляется
    $mail->addAddress($email); // $email - почта пользователя, введенная при регистрации

    // Контент письма
    $mail->isHTML(true);
    $mail->Subject = 'Подтверждение регистрации';
    $mail->Body    = "Нажмите на ссылку для подтверждения вашего аккаунта: 
                      <a href='http://lnk.monster/confirm.php?email=$email&token=$token'>Подтвердить регистрацию</a>";

    // Отправка письма
    $mail->send();
    echo 'Письмо с подтверждением отправлено на вашу почту.';
} catch (Exception $e) {
    echo "Ошибка отправки письма: {$mail->ErrorInfo}";
}