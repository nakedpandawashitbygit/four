<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Авторизация</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  </head>
  <body>
    <div class="container mt-5">
      <h1 class="text-center">Авторизация</h1>
      
      <!-- Навигация по вкладкам -->
      <ul class="nav nav-pills nav-justified mb-3">
        <li class="nav-item">
          <a class="nav-link <?php echo (!isset($_SESSION['active_tab']) || $_SESSION['active_tab'] == 'login') ? 'active' : ''; ?>" data-bs-toggle="pill" href="#login-tab">Вход</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (isset($_SESSION['active_tab']) && $_SESSION['active_tab'] == 'register') ? 'active' : ''; ?>" data-bs-toggle="pill" href="#register-tab">Регистрация</a>
        </li>
      </ul>

      <div class="tab-content">
        <!-- Вкладка Логина -->
        <div id="login-tab" class="tab-pane <?php echo (!isset($_SESSION['active_tab']) || $_SESSION['active_tab'] == 'login') ? 'active' : 'fade'; ?>">
          <div id="login-alert"></div>
          
          <form id="login-form">
            <div class="form-group">
              <label for="login_email">Электронная почта</label>
              <input type="email" class="form-control" id="login_email" name="login_email" required>
            </div>
            <div class="form-group position-relative">
              <label for="login_password">Пароль</label>
              <input type="password" class="form-control" id="login_password" name="login_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Войти</button>
          </form>
        </div>

        <!-- Вкладка Регистрации -->
        <div id="register-tab" class="tab-pane <?php echo (isset($_SESSION['active_tab']) && $_SESSION['active_tab'] == 'register') ? 'active' : 'fade'; ?>">
          <div id="register-alert"></div>
          
          <form id="register-form">
            <div class="form-group">
              <label for="register_email">Электронная почта</label>
              <input type="email" class="form-control" id="register_email" name="register_email" required>
            </div>
            <div class="form-group position-relative">
              <label for="register_password">Пароль</label>
              <input type="password" class="form-control" id="register_password" name="register_password" required>
            </div>
            <div class="form-group position-relative">
              <label for="register_confirm_password">Подтвердите пароль</label>
              <input type="password" class="form-control" id="register_confirm_password" name="register_confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Подключение Bootstrap JS для работы вкладок -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>

      // Проверка совпадения паролей при регистрации
      const registerForm = document.querySelector('#register-form');
      registerForm.addEventListener('submit', function(event) {
        let password = document.querySelector('#register_password').value;
        let confirmPassword = document.querySelector('#register_confirm_password').value;

        if (password !== confirmPassword) {
          event.preventDefault();
          alert('Пароли не совпадают!');
        }
      });

      // Обработка отправки формы регистрации
      $('#register-form').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
          type: 'POST',
          url: 'auth.php',
          data: $(this).serialize() + '&register=1',
          success: function(response) {
            $('#register-alert').html(response);
          },
          error: function() {
            $('#register-alert').html('<div class="alert alert-danger">Произошла ошибка. Попробуйте еще раз.</div>');
          }
        });
      });

      // Обработка отправки формы входа
      $('#login-form').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
          type: 'POST',
          url: 'auth.php',
          data: $(this).serialize() + '&login=1',
          success: function(response) {
            $('#login-alert').html(response);
          },
          error: function() {
            $('#login-alert').html('<div class="alert alert-danger">Произошла ошибка. Попробуйте еще раз.</div>');
          }
        });
      });
    </script>
  </body>
</html>