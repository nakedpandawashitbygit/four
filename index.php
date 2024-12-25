<?php

// запускаем сессию только если еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключение к базе данных и импорт необходимых файлов
require_once 'config.php';
require_once 'phpqrcode/qrlib.php'; // Если используется библиотека для генерации QR-кодов

// Инициализация переменных
$shortLink = '';
$qrCodePath = '';

// Получение новостей из базы данных
$newsItems = [];
$result = $conn->query("SELECT id, title, content FROM news ORDER BY created_at DESC");
if ($result) {
    $newsItems = $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['link'])) {
    $longUrl = $_POST['link'];

    // Генерация уникального идентификатора ссылки
    $shortCode = substr(md5(uniqid(rand(), true)), 0, 6);
    $shortLink = "https://lnk.monster/$shortCode";

    // Сохранение в базе данных
    $stmt = $conn->prepare("INSERT INTO four (long_url, short_url) VALUES (?, ?)");
    if (!$stmt) {
        die('Ошибка подготовки запроса: ' . $conn->error);
    }
    $stmt->bind_param("ss", $longUrl, $shortCode);
    $stmt->execute();

    // Получение ID записи
    $id = $stmt->insert_id;

    // Генерация QR-кода
    $qrCodePath = 'qrcodes/' . $id . '.png';
    QRcode::png($shortLink, $qrCodePath, QR_ECLEVEL_L, 20);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lnk.monster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .main-container {
            min-height: 100svh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #333;
            background-color: #f8f9fa;
        }
        .cta-text {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .input-group, .result-card {
            max-width: 600px;
            width: 100%;
            animation: fadeInUp 1.5s ease;
        }
        .news-carousel {
        position: fixed;
        bottom: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 10px 20px;
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.3);
        }
        .news-carousel .carousel-item {
            text-align: center;
        }
        .news-carousel .news-title {
        font-size: 1.25rem;
        color: #333;
        }
        .news-carousel .news-item {
        max-width: 600px;
        margin: 0 auto;
        text-align: left;
        }
        .news-carousel .news-content {
        font-size: 1rem;
        color: #555;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-size: 50%;
            width: 3rem;
            height: 3rem;
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    
    
    
    <!-- Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow">
    <div class="container">
        <a class="navbar-brand" href="#">lnk.monster</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!--
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">ltl.link</a>
                </li>
                -->
<?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard">dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="lk.php">profile</a>
                </li>
<?php endif; ?>

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="logs/app_log.txt">app_log.txt</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="addnews.php">add news</a>
                </li>
<?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="https://t.me/nakedpandawashitbytelegram" target="_blank">support</a>
                </li>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <li class="nav-item">
        <a class="nav-link" href="logout.php">logout</a>
    </li>
<?php else: ?>
    <li class="nav-item">
        <a class="nav-link" href="login.php">login</a>
    </li>
<?php endif; ?>

            </ul>
        </div>
    </div>
</nav>



    
    

    <div class="main-container">
    <h1 class="cta-text">Make Your Links Roar with <a href="dashboard" style="text-decoration: none; color: inherit;"><em>lnk.monster!</em></a></h1>
    
    <!-- Форма для ввода ссылки -->
    <form class="input-group" action="" method="POST">
        <input type="url" class="form-control" placeholder="Enter your link" name="link" required>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>

    <?php if ($shortLink): ?>
        <!-- Карточка с короткой ссылкой и QR-кодом -->
        <div class="result-card card mt-5 p-4">
            <p><a href="<?= $shortLink ?>" target="_blank"><?= $shortLink ?></a></p>
            <!-- Превью QR-кода -->
            <a href="<?= $qrCodePath ?>" target="_blank">
                <img src="<?= $qrCodePath ?>" alt="QR Code" style="width: 200px; height: 200px; object-fit: contain;">
            </a>
            <!-- Кнопка для скачивания -->
            <button class="btn btn-secondary mt-3">
                <a href="<?= $qrCodePath ?>" download style="text-decoration: none; color: white;">Download QR Code</a>
            </button>
        </div>
    <?php endif; ?>
    </div>

    <!-- Карусель новостей
    <div id="newsCarousel" class="carousel slide news-carousel" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($newsItems as $index => $news): ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                <div class="news-item p-3 rounded bg-light shadow-sm">
                    <h6 class="news-title fw-bold mb-2"><?= htmlspecialchars($news['title']) ?></h6>
                    <p class="news-content text-muted mb-0"><?= htmlspecialchars($news['content']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
    </div>
    -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>