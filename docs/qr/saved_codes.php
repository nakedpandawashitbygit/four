<?php
session_start(); // Начинаем сессию

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Если не авторизован, перенаправляем на страницу входа
    exit();
}

if (isset($_SESSION['user_id'])) {
    // Пользователь авторизован, показываем кнопки
    echo 'Добро пожаловать, ' . htmlspecialchars($_SESSION['username']) . '! ';
    echo '<button onclick="window.location.href=\'index.php\'">На главную</button> ';
    echo '<button onclick="window.location.href=\'set_session.php?action=logout\'">Выйти</button> ';
}

// Подключаем файл с конфигурацией базы данных
include_once 'config.php';

// Получаем сохраненные QR-коды из базы данных
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, qr_code_data, qr_image_data, visit_count FROM qr_codes WHERE user_id = ? ORDER BY id DESC"); // Сортируем по убыванию ID
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Создаем массив для сохраненных QR-кодов
$qr_codes = [];
while ($row = $result->fetch_assoc()) {
    $qr_codes[] = $row; // Сохраняем всю строку, включая id, qr_image_data и visit_count
}

// Закрываем запрос и соединение
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сохранённые QR-коды</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        .qr-code {
            display: inline-block;
            margin: 10px;
            text-align: center;
        }
        .qr-code p {
            margin-top: 5px;
            font-weight: bold;
        }
        .qr-code button {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>Сохранённые QR-коды</h1>
    <div id="qrCodesContainer">
        <?php if (empty($qr_codes)): ?>
            <p>У вас нет сохранённых QR-кодов.</p>
        <?php else: ?>
            <?php foreach ($qr_codes as $qr_code): ?>
                <div class="qr-code">
                    <p><?php echo htmlspecialchars($qr_code['qr_code_data']); ?></p>
                    <div class="qr-code-img" data-text="<?php echo htmlspecialchars($qr_code['qr_code_data']); ?>"></div> <!-- Контейнер для QR-кода -->
                    <p>Переходов: <?php echo htmlspecialchars($qr_code['visit_count']); ?></p> <!-- Количество переходов -->
                    <button onclick="deleteQRCode(<?php echo $qr_code['id']; ?>)">Удалить</button> <!-- Кнопка удаления -->
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.qr-code-img').forEach(function(element) {
            const text = element.getAttribute('data-text');
            const qr = qrcode(0, 'M');
            qr.addData(text);
            qr.make();
            element.innerHTML = qr.createImgTag(5); // Генерируем изображение QR-кода и добавляем его в контейнер
        });

        function deleteQRCode(id) {
            if (confirm('Вы уверены, что хотите удалить этот QR-код?')) {
                fetch('delete_qr.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('QR-код успешно удалён!');
                        location.reload(); // Перезагружаем страницу, чтобы обновить список
                    } else {
                        alert('Ошибка при удалении QR-кода: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Ошибка при удалении QR-кода');
                });
            }
        }
    </script>
</body>
</html>