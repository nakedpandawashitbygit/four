<?php
session_start();
require_once 'config.php';
require_once 'functions.php'; // Соединение с базой данных

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Получение данных пользователя из базы данных
$query = "SELECT username, created_at FROM users WHERE ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $created_at);
$stmt->fetch();
$stmt->close();

// Обновление имени пользователя
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_username'])) {
    $new_username = $_POST['username'];
    $update_query = "UPDATE users SET username = ? WHERE ID = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_username, $userId);
    $stmt->execute();
    $stmt->close();
    $username = $new_username; // Обновляем переменную
}

// Обновление пароля
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $update_password_query = "UPDATE users SET password = ? WHERE ID = ?";
    $stmt = $conn->prepare($update_password_query);
    $stmt->bind_param("si", $new_password, $userId);
    $stmt->execute();
    $stmt->close();
}

// Запрос для подсчета количества ссылок, созданных пользователем
$link_count_query = "SELECT COUNT(*) as link_count FROM four WHERE user_id = ?";
$stmt = $conn->prepare($link_count_query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($link_count);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Основной стиль страницы */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
        }
        .container {
            display: flex;
        }
        .menu {
            width: 200px;
            background-color: #f0f0f0;
            padding: 20px;
            transition: width 0.3s;
        }
        .menu.collapsed {
            width: 40px; /* Ширина для свернутого меню */
        }
        .menu a {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            text-decoration: none;
            color: black;
            white-space: nowrap;
            overflow: hidden;
            transition: all 0.3s;
        }
        .menu.collapsed a span {
            display: none; /* Скрыть текст, когда меню свернуто */
        }
        .menu.collapsed a i {
            margin-right: 0; /* Убираем отступ у иконок */
        }
        .menu a i {
            margin-right: 10px; /* Отступ иконки от текста */
        }
        .menu-toggle {
            cursor: pointer;
            text-align: right;
            margin-bottom: 20px;
        }
        .menu a:hover {
            background-color: #ddd;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .profile-info {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="menu" id="menu">
    <div class="menu-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </div>
    <a href="dashboard.php" style="color: green; margin-top: 20px;">
        <i class="fas fa-link"></i> <span>Ссылки</span>
    </a>
    <a href="#" onclick="showProfile()">
        <i class="fas fa-user"></i> <span>Профиль</span>
    </a>
    <a href="#" onclick="showTariffs()">
        <i class="fas fa-tags"></i> <span>Тарифы</span>
    </a>
    <a href="logout.php" style="color: red; margin-top: 20px;">
        <i class="fas fa-sign-out-alt"></i> <span>Выйти</span>
    </a>
    </div>

    <div class="content" id="content">
        <!-- Профиль -->
        <div id="profile">
            <h2>Профиль</h2>
            <div class="profile-info">
                <p><strong>Имя пользователя:</strong> <?php echo htmlspecialchars($username); ?></p>
                <p><strong>Дата создания профиля:</strong> <?php echo htmlspecialchars($created_at); ?></p>
            </div>

            <form method="POST">
                <label for="username">Изменить имя пользователя:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <button type="submit" name="update_username">Обновить имя пользователя</button>
            </form>

            <form method="POST">
                <label for="password">Изменить пароль:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit" name="update_password">Обновить пароль</button>
            </form>
        </div>

        <!-- Тарифы -->
        <div id="tariffs" style="display: none;">
            <h2>Тарифы</h2>
            <p>Вы создали: <strong><?php echo $link_count; ?></strong> ссылок.</p>
        </div>
        
        <!-- Попытка вставить дашборд =) -->
        <div id="dashboard" style="display: none;">
            <h2>Dashboard.php</h2>
        </div>
        
    </div>
</div>

<script>
    function toggleMenu() {
        var menu = document.getElementById('menu');
        menu.classList.toggle('collapsed');
    }
    
    function showProfile() {
        document.getElementById('profile').style.display = 'block';
        document.getElementById('tariffs').style.display = 'none';
        document.getElementById('dashboard').style.display = 'none';
    }

    function showTariffs() {
        document.getElementById('profile').style.display = 'none';
        document.getElementById('tariffs').style.display = 'block';
        document.getElementById('dashboard').style.display = 'none';
    }
    
    function showDashboard() {
        document.getElementById('dashboard').style.display = 'block';
        document.getElementById('profile').style.display = 'none';
        document.getElementById('tariffs').style.display = 'none';
    }
</script>

</body>
</html>