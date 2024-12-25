<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$user_id = $_SESSION['user_id'];

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получаем данные пользователя из базы
$sql = "SELECT username, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Пользователь не найден.";
    exit();
}

// Получаем статистику пользователя
$sqlStats = "SELECT SUM(qr_count) AS total_qr, SUM(short_count) AS total_links FROM four WHERE user_id = ?";
$stmtStats = $conn->prepare($sqlStats);
$stmtStats->bind_param("i", $user_id);
$stmtStats->execute();
$resultStats = $stmtStats->get_result();
if ($resultStats->num_rows > 0) {
    $stats = $resultStats->fetch_assoc();
    $total_qr = $stats['total_qr'] ?? 0;
    $total_links = $stats['total_links'] ?? 0;
} else {
    $total_qr = 0;
    $total_links = 0;
}

// Подсчёт общего количества кликов (QR-коды + короткие ссылки)
$sqlClicks = "SELECT SUM(qr_count) + SUM(short_count) AS total_clicks FROM four WHERE user_id = ?";
$stmtClicks = $conn->prepare($sqlClicks);
if (!$stmtClicks) {
    die("SQL Error: " . $conn->error);
}
$stmtClicks->bind_param("i", $user_id);
$stmtClicks->execute();
$resultClicks = $stmtClicks->get_result();

if ($resultClicks->num_rows > 0) {
    $clicks = $resultClicks->fetch_assoc();
    $total_clicks = $clicks['total_clicks'] ?? 0;
} else {
    $total_clicks = 0;
}

//для первого графика
// собираем колво кликов для графика из таблиц
$sqlClicksPerMonth = "
SELECT 
    DATE_FORMAT(s.click_time, '%Y-%m') AS month,
    SUM(CASE WHEN s.source = 'qr' THEN 1 ELSE 0 END) AS total_qr_clicks,
    SUM(CASE WHEN s.source = 'short' THEN 1 ELSE 0 END) AS total_short_clicks
FROM STAT s
JOIN four f ON s.link_id = f.id
WHERE f.user_id = ?
GROUP BY month
ORDER BY month ASC;
";
$stmt = $conn->prepare($sqlClicksPerMonth);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$months = [];
$qrCounts = [];
$shortCounts = [];
while ($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $qrCounts[] = $row['total_qr_clicks'];
    $shortCounts[] = $row['total_short_clicks'];
}
// передаём данные по кликам в скрипт
echo "<script>
    const chartLabels = " . json_encode($months) . ";
    const qrScanData = " . json_encode($qrCounts) . ";
    const shortLinkData = " . json_encode($shortCounts) . ";
</script>";

//для второго графика
// собираем колво кликов для графика из таблицы four по устройствам
$sqlDeviceType = "
    SELECT device, COUNT(*) AS clicks
    FROM STAT
    JOIN four ON STAT.link_id = four.id
    WHERE four.user_id = ?
    GROUP BY device
";
$stmtDeviceType = $conn->prepare($sqlDeviceType);
$stmtDeviceType->bind_param("i", $user_id); // $user_id — ID текущего пользователя
$stmtDeviceType->execute();
$resultDeviceType = $stmtDeviceType->get_result();
$deviceTypes = [];
$deviceClicks = [];
while ($row = $resultDeviceType->fetch_assoc()) {
    $deviceTypes[] = $row['device'];
    $deviceClicks[] = $row['clicks'];
}

//для третьего графика
// Получение IP-адресов из базы данных
$query = "SELECT user_ip FROM STAT WHERE link_id IN (SELECT id FROM four WHERE user_id = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cityStats = []; // Массив для хранения статистики по городам
while ($row = $result->fetch_assoc()) {
    $ip = $row['user_ip'];
    $location = getGeoLocation($ip); // Вызов вашей функции из functions.php
    $city = $location['city'] ?? 'Unknown';
// Увеличиваем счетчик для текущего города
    if (!isset($cityStats[$city])) {
        $cityStats[$city] = 0;
    }
    $cityStats[$city]++;
}
// Вывод статистики по городам
foreach ($cityStats as $city => $count) {
    echo "Город: {$city}, Переходов: {$count}<br>";
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .profile-info, .statistics {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .header { background-color: #007bff; color: white; padding: 10px 0; }
    </style>
</head>
<body>

        <!-- Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">4OUR</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" href="index.php">index.php</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="dashboard">dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="lk.php">lk.php</a>
        </li>
        
        <li>
		<?php if ($_SESSION['user_role'] === 'admin'): ?>
		<a class="nav-link" href="logs/app_log.txt">app_log.txt</a>
        <?php endif; ?>
		</li>
					
		<li>
		<?php if ($_SESSION['user_role'] === 'admin'): ?>
		<a class="nav-link" href="addnews.php">addnews.php</a>
        <?php endif; ?>
		</li>
        
        <li class="nav-item">
          <a class="nav-link" href="logout.php">logout.php</a>
        </li>
      </ul>
    </div>
  </div>
</nav>


    <div class="container mt-5">
    <div class="row">
        <!-- User Information and Statistics -->
        <div class="col-md-4">
            <div class="profile-info mb-4">
                <h3>User Information</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Registration Date:</strong> <?= htmlspecialchars(date("F d, Y", strtotime($user['created_at']))) ?></p>
            </div>

            <div class="statistics mb-4">
                <h3>Statistics</h3>
                <div class="row">
                    <div class="col-4 text-center">
                        <h5>Clicks</h5>
                        <p><?= $total_links ?></p>
                    </div>
                    <div class="col-4 text-center">
                        <h5>Scanned</h5>
                        <p><?= $total_qr ?></p>
                    </div>
                    <div class="col-4 text-center">
                        <h5>Total</h5>
                        <p><?= $total_clicks ?></p>
                    </div>
                </div>
            </div>

            <!-- Account Settings Section -->
            <div class="profile-info mb-4">
                <h3>Account Settings</h3>
                <form action="update_settings.php" method="POST">
                    <div class="mb-3">
                        <label for="new-password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new-password" name="new-password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

<!-- DIVы графиков -->
<div class="col-md-8">
    <div class="mb-4">
        <h3>Click Statistics</h3>
        <canvas id="clickChart" width="400" height="200"></canvas>
    </div>
    <div class="mb-4">
        <h3>Device Type Statistics</h3>
        <canvas id="deviceChart" width="400" height="200"></canvas>
    </div>
    <div class="mb-4">
        <h3>Clicks by City</h3>
        <canvas id="cityStats" width="400" height="200"></canvas>
    </div>
</div>

 <!-- скрипты для третьего графика -->
<script>
const cityData = <?= json_encode($cityStats); ?>;
console.log(cityData);
</script>
<script>
const cityStats = <?php echo json_encode($cityStats); ?>;
// Данные для графика
const labels = Object.keys(cityStats);
const data = Object.values(cityStats);
// Создание графика
const ctx = document.getElementById('cityStats').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Количество переходов',
            data: data,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>



<!-- скрипты для первого графика -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const chartLabels = <?php echo json_encode($months); ?>;
    const qrScanData = <?php echo json_encode($qrCounts); ?>;
    const shortLinkData = <?php echo json_encode($shortCounts); ?>;

    const data = {
        labels: chartLabels,
        datasets: [
            {
                label: "QR Code Scans",
                data: qrScanData,
                borderColor: "blue",
                backgroundColor: "rgba(0, 0, 255, 0.1)",
                fill: true,
            },
            {
                label: "Short Link Clicks",
                data: shortLinkData,
                borderColor: "green",
                backgroundColor: "rgba(0, 255, 0, 0.1)",
                fill: true,
            },
        ],
    };

    const config = {
        type: "line",
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "top",
                },
            },
        },
    };

    new Chart(document.getElementById("clickChart"), config);
});
</script>

<!-- скрипты для второго графика -->
<script>
// Данные для графика по типам устройств
const deviceChartLabels = <?php echo json_encode($deviceTypes); ?>; // Метки для оси X
const deviceChartData = <?php echo json_encode($deviceClicks); ?>; // Количество кликов для каждого устройства
// Создаём массив цветов для разных устройств
const colors = {
    desktop: { borderColor: "blue", backgroundColor: "rgba(0, 0, 255, 0.1)" },
    mobile: { borderColor: "green", backgroundColor: "rgba(0, 255, 0, 0.1)" },
    tablet: { borderColor: "orange", backgroundColor: "rgba(255, 165, 0, 0.1)" },
};
const datasets = deviceChartLabels.map((label, index) => ({
    label: label, // Название устройства
    data: [deviceChartData[index]], // Данные для устройства
    borderColor: colors[label]?.borderColor || "gray", // Цвет для рамки
    backgroundColor: colors[label]?.backgroundColor || "rgba(128, 128, 128, 0.1)", // Цвет заливки
    fill: true,
}));
const deviceData = {
    labels: ["Device Types"], // Ось X (одна общая метка)
    datasets: datasets, // Массив с каждым типом устройства
};
const deviceConfig = {
    type: "bar", // Столбчатая диаграмма
    data: deviceData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: "top",
            },
        },
        scales: {
            x: {
                title: {
                    display: false,
                    text: "Device Types", // Название оси X
                },
            },
            y: {
                
                ticks: {
                stepSize: 1, // Устанавливаем шаг меток
                beginAtZero: true, // Начало с нуля
                },
                
                title: {
                    display: true,
                    text: "Clicks", // Название оси Y
                },
            },
        },
    },
};
// Инициализация графика
new Chart(document.getElementById("deviceChart"), deviceConfig);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>