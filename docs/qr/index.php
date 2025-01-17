<?php
session_start(); // Начинаем сессию

// Проверяем, авторизован ли пользователь
if (isset($_SESSION['user_id'])) {
    // Пользователь авторизован, показываем кнопки
    echo 'Добро пожаловать, ' . htmlspecialchars($_SESSION['username']) . '! ';
    echo '<button onclick="window.location.href=\'saved_codes.php\'">Показать сохранённые QR-коды</button> ';
    echo '<button onclick="window.location.href=\'set_session.php?action=logout\'">Выйти</button> ';
} else {
    // Пользователь не авторизован, показываем кнопку для входа
    echo 'Вы не авторизованы ';
    echo '<button onclick="window.location.href=\'login.html\'">Войти</button>';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Генератор QR-кодов</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        /* Скрываем кнопку "Сохранить QR-код" по умолчанию */
        #saveQrCode {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div id="authStatus"></div>
    <div id="content">
        <h1>Генератор QR-кодов</h1>
        <form id="qrForm">
            <label for="qrText">Введите текст для QR-кода:</label>
            <input type="text" id="qrText" name="qrText" required>
            <button type="submit">Создать QR-код</button>
        </form>
        <div id="qrCode"></div>
        <button id="saveQrCode">Сохранить QR-код</button>
        <input type="hidden" id="userId" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveButton = document.getElementById('saveQrCode');
            
            // Убедимся, что кнопка скрыта при загрузке страницы
            saveButton.style.display = 'none';

            const qrForm = document.getElementById('qrForm');
            const userId = document.getElementById('userId').value;

            qrForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Предотвращаем стандартное поведение формы
                const qrText = document.getElementById('qrText').value;
                const qrDiv = document.getElementById('qrCode');
                qrDiv.innerHTML = ''; // Очищаем предыдущий QR-код
                
                // Генерация QR-кода
                const qr = qrcode(0, 'M');
                qr.addData(qrText);
                qr.make();
                
                // Отображаем QR-код в div
                const img = qr.createImgTag(5);
                qrDiv.innerHTML = img;
                
                // Показываем кнопку "Сохранить QR-код"
                saveButton.style.display = 'inline';
                
                // Обработка нажатия на кнопку "Сохранить QR-код"
                saveButton.onclick = function() {
                    saveQrCodeToServer(userId, qrText);
                };
            });

            function saveQrCodeToServer(userId, qrText) {
                const data = {
                    user_id: userId,
                    text: qrText
                };

                fetch('save_qr.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при сохранении QR-кода:', error);
                });
            }
        });
    </script>
</body>
</html>