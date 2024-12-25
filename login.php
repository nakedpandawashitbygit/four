<?php
session_start();

if (isset($_SESSION['user_id']) || isset($_COOKIE['auth_token'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Авторизация</title>
    <style>
      body {
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
      }
      .auth-container {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        width: 100%;
      }
      .form-control {
        border-radius: 30px;
      }
      .btn {
        border-radius: 30px;
      }
      .social-btn {
        border-radius: 30px;
        padding: 10px;
        font-size: 14px;
        margin-top: 10px;
      }
      .or-divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 20px 0;
      }
      .or-divider::before,
      .or-divider::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid #ddd;
      }
      .or-divider:not(:empty)::before {
        margin-right: .75em;
      }
      .or-divider:not(:empty)::after {
        margin-left: .75em;
      }
      .verify-email-screen {
        text-align: center;
        padding: 40px;
      }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  </head>
  <body>

    <div class="auth-container" id="auth-container">
      <h2 class="text-center" id="auth-title">С возвращением</h2>

      <!-- Вкладка Логина -->
      <form id="login-form">
        <div class="form-group">
          <label for="login_email">Адрес электронной почты</label>
          <input type="email" class="form-control" id="login_email" name="login_email" required>
        </div>
        <div class="form-group mt-3">
          <label for="login_password">Пароль</label>
          <input type="password" class="form-control" id="login_password" name="login_password" required>
        </div>
        <button type="submit" class="btn btn-success btn-block mt-4">Продолжить</button>
		
		<div class="text-center mt-3">
        <small>У вас нет учетной записи? <a href="#" id="show-register">Зарегистрироваться</a></small>
		</div>
		
      </form>

      <!-- Форма Регистрации -->
      <form id="register-form" style="display:none;">
        <div class="form-group">
          <label for="register_email">Адрес электронной почты</label>
          <input type="email" class="form-control" id="register_email" name="register_email" required>
        </div>
        <div class="form-group mt-3">
          <label for="register_password">Пароль</label>
          <input type="password" class="form-control" id="register_password" name="register_password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-4">Зарегистрироваться</button>
		
		<div class="text-center mt-3">
        <small>Уже есть учётная запись? <a href="#" id="show-login">Войти</a></small>
		</div>
		
      </form>

      <div class="or-divider">ИЛИ</div>

      <button class="btn btn-outline-dark btn-block social-btn">
        <i class="fab fa-google"></i> Продолжить с Google
      </button>
      <button class="btn btn-outline-dark btn-block social-btn">
        <i class="fab fa-microsoft"></i> Продолжить с учетной записью Microsoft
      </button>
      <button class="btn btn-outline-dark btn-block social-btn">
        <i class="fab fa-apple"></i> Продолжить с Apple
      </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      // Показ формы регистрации
      $('#show-register').on('click', function(e) {
        e.preventDefault();
        $('#login-form').hide();
        $('#register-form').show();
        $('#auth-title').text('Регистрация');
      });

      // Показ формы логина
      $('#show-login').on('click', function(e) {
        e.preventDefault();
        $('#register-form').hide();
        $('#login-form').show();
        $('#auth-title').text('С возвращением');
      });

      // Обработка отправки формы входа
      $('#login-form').on('submit', function(event) {
        event.preventDefault(); // Останавливаем стандартное поведение формы

        const formData = $(this).serialize(); // Получаем данные формы

        $.ajax({
          type: 'POST',
          url: 'auth.php', // Адрес обработки данных на сервере
          data: formData + '&login=1', // Отправляем данные на сервер
          success: function(response) {
            if (response.includes('Вход успешен!')) {
              window.location.href = 'dashboard'; // Редирект на dashboard
            } else if (response.includes('Ваш аккаунт не активирован')) {
              alert('Ваш аккаунт не активирован. Пожалуйста, проверьте вашу почту для подтверждения.');
            } else if (response.includes('Неверная электронная почта или пароль')) {
              alert('Неверная электронная почта или пароль.');
            } else {
              alert('Произошла ошибка. Попробуйте еще раз.');
            }
          },
          error: function() {
            alert('Произошла ошибка. Попробуйте еще раз.');
          }
        });
      });

      
	  // Обработка отправки формы регистрации
$('#register-form').on('submit', function(event) {
    event.preventDefault(); // Останавливаем стандартное поведение формы

    const formData = $(this).serialize(); // Получаем данные формы

    $.ajax({
        type: 'POST',
        url: 'auth.php', // Адрес обработки данных на сервере
        data: formData + '&register=1', // Отправляем данные на сервер
        success: function(response) {
            if (response.includes('Регистрация успешна!')) {
                const email = $('#register_email').val(); // Сохраняем email для повторной отправки
                // Показываем экран подтверждения email
                $('#auth-container').html(`
                    <div class="verify-email-screen">
                        <h2>Подтвердите ваш адрес электронной почты</h2>
                        <p>Мы отправили электронное письмо на адрес ${email}.</p>
                        <p>Нажмите на ссылку внутри, чтобы начать.</p>
                        <button class="btn btn-dark mt-3" onclick="window.location.href='https://mail.google.com'">Открыть Gmail</button>
                        <a href="#" id="resend-email" class="d-block mt-3">Отправить электронное письмо повторно</a>
                    </div>
                `);

                // Обработчик для повторной отправки электронного письма
                $('#resend-email').on('click', function(e) {
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: 'auth.php',
                        data: { resend: 1, email: email },
                        success: function(response) {
                            if (response.includes('Письмо отправлено повторно!')) {
                                alert('Письмо было отправлено повторно.');
                            } else {
                                alert('Не удалось отправить письмо. Попробуйте еще раз.');
                            }
                        },
                        error: function() {
                            alert('Произошла ошибка при повторной отправке. Попробуйте еще раз.');
                        }
                    });
                });

            } else if (response.includes('Этот адрес электронной почты уже зарегистрирован')) {
                alert('Этот адрес электронной почты уже зарегистрирован.');
            } else {
                alert('Ошибка регистрации. Попробуйте еще раз.');
            }
        },
        error: function() {
            alert('Произошла ошибка. Попробуйте еще раз.');
        }
    });
});

	  
	  
    </script>

  </body>
</html>