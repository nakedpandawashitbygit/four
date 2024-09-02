<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Удаление истекших ссылок
$conn->query("DELETE FROM four WHERE expiration_date IS NOT NULL AND expiration_date <= NOW()");

// Обработка удаления ссылки
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $short_url = $_GET['short_url'];
    
    // Удаление записи из базы данных
    $stmt = $conn->prepare("DELETE FROM four WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Удаление QR-кода
    @unlink("qrcodes/$id.png");
    
    header("Location: dashboard.php");
    exit();
}

// Обработка редактирования ссылки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $new_long_url = $_POST['new_long_url'];
    $new_short_url = $_POST['new_short_url'];
    //$new_password = $_POST['new_password'];

    // Проверка на уникальность короткой ссылки
    $stmt = $conn->prepare("SELECT COUNT(*) FROM four WHERE short_url = ? AND id != ?");
    $stmt->bind_param("si", $new_short_url, $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "Ошибка: Короткая ссылка уже существует.";
    } else {
        // Обновление записи в базе данных
        $stmt = $conn->prepare("UPDATE four SET long_url = ?, short_url = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_long_url, $new_short_url, $id);
        
        if ($stmt->execute()) {
            // Генерация нового QR-кода
            require_once 'phpqrcode/qrlib.php';
            QRcode::png("http://h406470147.nichost.ru/r.php?id=$id", "qrcodes/$id.png");
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Ошибка при обновлении: " . $stmt->error; // Вывод ошибки
        }
        
        $stmt->close();
    }
}

// Создание новой ссылки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $long_url = $_POST['long_url'];
    $short_url = shortenUrl();
    $expiration_date = $_POST['expiration_date'] ? $_POST['expiration_date'] : null;
    $link_password = $_POST['link_password'] ? $_POST['link_password'] : null;

    $stmt = $conn->prepare("INSERT INTO four (user_id, long_url, short_url, expiration_date, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $_SESSION['user_id'], $long_url, $short_url, $expiration_date, $link_password);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id; // Получаем ID только что созданной ссылки
        // Генерация QR-кода
        require_once 'phpqrcode/qrlib.php';
        QRcode::png("http://h406470147.nichost.ru/r.php?id=$id", "qrcodes/$id.png");
    } else {
        echo "Ошибка: " . $stmt->error;
    }
    $stmt->close();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM four WHERE user_id = $user_id ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        h2 { color: #333; }
        h3 { color: #333; }
        form { margin-bottom: 20px; }
        input, button { padding: 10px; margin: 5px 0; }
        .link-container {
            border: 1px solid black; /* Черная рамка 1px */
            padding: 10px;
            margin: 10px 0;
            display: flex;
            align-items: flex-start;
        }
        .qrcode {
            margin-right: 20px;
        }
        .qrcode img {
            display: block;
            width: 100px; /* Установите нужный размер QR-кода */
            height: 100px; /* Установите нужный размер QR-кода */
        }
        .link-info {
            display: flex;
            flex-direction: column;
        }
        .short-url, .long-url, .expiration_date, .password {
            margin: 5px 0;
        }
        .actions {
            margin-top: 10px;
        }
        .delete-btn, .edit-btn {
            margin-right: 10px;
            cursor: pointer;
            text-decoration: none;
            color: blue;
        }
        .edit-input, .save-btn {
            display: none; /* Скрыть поле ввода и кнопку по умолчанию */
        }
    </style>
</head>
<body>
    
    <h1>Личный кабинет</h1>
    <h3>Для ссылки задаётся уникальный ID, которому выдаётся короткая ссылка и QR-код</h3>
    
    <form method="POST">
        <input type="text" name="long_url" placeholder="Введите ссылку" required>
        <input type="datetime-local" name="expiration_date" placeholder="Срок действия (необязательно)">
        <input type="password" name="link_password" placeholder="Пароль (необязательно)">
        <button type="submit">Сделать</button>
    </form>

    <h2>Ваши ссылки</h2>
    <div>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="link-container">
                <div class="qrcode">
                    <img src="qrcodes/<?php echo $row['id']; ?>.png" alt="QR-код для ссылки <?php echo $row['id']; ?>" />
                </div>
                <div class="link-info">
                <div class="short-url">
                <strong>Короткая ссылка:</strong>
                <span class="short-url-text"><a href="http://h406470147.nichost.ru/<?php echo $row['short_url']; ?>" target="_blank">http://h406470147.nichost.ru/<?php echo $row['short_url']; ?> </a></span>
                <input type="text" class="edit-input" placeholder="Введите новую короткую ссылку" />
                <button class="save-btn" onclick="saveEdit(<?php echo $row['id']; ?>, this)">Сохранить</button>
                </div>
                    
                <div class="long-url">
                <strong>Длинная ссылка:</strong>
                <span class="long-url-text"><a href="<?php echo $row['long_url']; ?>" target="_blank"><?php echo $row['long_url']; ?> </a></span>
                <input type="text" class="edit-input" placeholder="Введите новую длинную ссылку" />
                <button class="save-btn" onclick="saveEdit(<?php echo $row['id']; ?>, this)">Сохранить</button>
                </div>
                
                <?php if ($row['expiration_date']): ?>
                <div class="expiration-date">
                <strong>Срок действия:</strong> <?php echo $row['expiration_date']; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($row['password']): ?>
                <div class="link-password">
                <strong>Пароль для доступа:</strong> <?php echo $row['password']; ?>
                </div>
                <?php endif; ?>
                
                <div class="actions">
                <a href="dashboard.php?delete=<?php echo $row['id']; ?>&short_url=<?php echo $row['short_url']; ?>" class="delete-btn">Удалить</a>
                <button type="button" class="edit-btn" onclick="showEditForm(<?php echo $row['id']; ?>, '<?php echo $row['long_url']; ?>', '<?php echo $row['short_url']; ?>', this)">Редактировать</button>
                </div>
                
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <script>
        function showEditForm(id, long_url, short_url, button) {
            const longUrlText = button.parentElement.parentElement.querySelector('.long-url-text');
            const shortUrlText = button.parentElement.parentElement.querySelector('.short-url-text');
            const longEditInput = button.parentElement.parentElement.querySelector('.long-url .edit-input');
            const shortEditInput = button.parentElement.parentElement.querySelector('.short-url .edit-input');
            const saveButtons = button.parentElement.parentElement.querySelectorAll('.save-btn');

            if (longEditInput.style.display === "none" || longEditInput.style.display === "") {
                longUrlText.style.display = "none"; // Скрыть текст длинной ссылки
                shortUrlText.style.display = "none"; // Скрыть текст короткой ссылки
                longEditInput.style.display = "inline"; // Показать поле ввода длинной ссылки
                shortEditInput.style.display = "inline"; // Показать поле ввода короткой ссылки
                saveButtons.forEach(btn => btn.style.display = "inline"); // Показать кнопки "Сохранить"
                longEditInput.value = long_url; // Заполнить поле текущим значением
                shortEditInput.value = short_url; // Заполнить поле текущим значением
                button.textContent = "Отмена"; // Изменить текст кнопки
                button.setAttribute("onclick", `cancelEdit(${id}, '${long_url}', '${short_url}', this)`); // Изменить обработчик события
            } else {
                longUrlText.style.display = "block"; // Показать текст длинной ссылки
                shortUrlText.style.display = "block"; // Показать текст короткой ссылки
                longEditInput.style.display = "none"; // Скрыть поле ввода длинной ссылки
                shortEditInput.style.display = "none"; // Скрыть поле ввода короткой ссылки
                saveButtons.forEach(btn => btn.style.display = "none"); // Скрыть кнопки "Сохранить"
                button.textContent = "Редактировать"; // Вернуть текст кнопки
                button.setAttribute("onclick", `showEditForm(${id}, '${long_url}', '${short_url}', this)`); // Вернуть обработчик события
            }
        }

        function saveEdit(id, button) {
            const longEditInput = button.parentElement.parentElement.querySelector('.long-url .edit-input');
            const shortEditInput = button.parentElement.parentElement.querySelector('.short-url .edit-input');
            const newLongUrl = longEditInput.value;
            const newShortUrl = shortEditInput.value;

            // Создаем скрытую форму для отправки данных
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'dashboard.php';

            const editIdInput = document.createElement('input');
            editIdInput.type = 'hidden';
            editIdInput.name = 'edit_id';
            editIdInput.value = id;
            form.appendChild(editIdInput);

            const newLongUrlInput = document.createElement('input');
            newLongUrlInput.type = 'hidden';
            newLongUrlInput.name = 'new_long_url';
            newLongUrlInput.value = newLongUrl;
            form.appendChild(newLongUrlInput);

            const newShortUrlInput = document.createElement('input');
            newShortUrlInput.type = 'hidden';
            newShortUrlInput.name = 'new_short_url';
            newShortUrlInput.value = newShortUrl;
            form.appendChild(newShortUrlInput);

            document.body.appendChild(form);
            form.submit(); // Отправляем форму
        }

        function cancelEdit(id, long_url, short_url, button) {
            showEditForm(id, long_url, short_url, button);
        }
    </script>
</body>
</html>

<?php
if ($conn) {
    $conn->close(); // Закрываем соединение, если оно было успешно создано
}
?>
