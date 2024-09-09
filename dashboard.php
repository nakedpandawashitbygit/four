<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Удаление истекших ссылок
$conn->query("DELETE FROM four WHERE expiration_date IS NOT NULL AND expiration_date <= NOW()");

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $short_url = $_GET['short_url'];

    $stmt = $conn->prepare("DELETE FROM four WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    @unlink("qrcodes/$id.png");
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $new_long_url = $_POST['new_long_url'];
    $new_short_url = $_POST['new_short_url'];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM four WHERE short_url = ? AND id != ?");
    $stmt->bind_param("si", $new_short_url, $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "Ошибка: Короткая ссылка уже существует.";
    } else {
        $stmt = $conn->prepare("UPDATE four SET long_url = ?, short_url = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_long_url, $new_short_url, $id);
        
        if ($stmt->execute()) {
            require_once 'phpqrcode/qrlib.php';
            QRcode::png("http://h406470147.nichost.ru/r.php?id=$id", "qrcodes/$id.png");
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Ошибка при обновлении: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $long_url = $_POST['long_url'];
    $short_url = shortenUrl();
    $title = $_POST['title'];
    $comment = $_POST['comment'];
    $expiration_date = $_POST['expiration_date'] ? $_POST['expiration_date'] : null;
    $link_password = $_POST['link_password'] ? $_POST['link_password'] : null;

    $stmt = $conn->prepare("INSERT INTO four (user_id, long_url, short_url, title, comment, expiration_date, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $_SESSION['user_id'], $long_url, $short_url, $title, $comment, $expiration_date, $link_password);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
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

<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
  <title>Dashboard</title>
  <style>
    .navbar-custom {
      background-color: #fdcb04; /* Жёлтый цвет меню */
    }
    .user-icon {
      width: 40px;
      height: 40px;
      background-color: #fff;
      border-radius: 50%;
      display: inline-block;
      text-align: center;
      line-height: 40px;
      font-weight: bold;
      color: #000;
    }
    .navbar-custom .container {
      max-width: 960px; /* Ширина меню */
    }
  </style>
</head>
<body>
  <!-- Навигационное меню -->
  <nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid justify-content-between">
      <a class="navbar-brand" href="#">ltl.link</a>
      <div class="dropdown">
        <a href="#" class="d-block link-dark text-decoration-none" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="user-icon">
            <img src="user_icon.png" alt="User Icon" class="img-fluid" style="width: 100%; height: 100%;">
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end text-small" aria-labelledby="dropdownUser1">
          <li><a class="dropdown-item" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>
  
  <div class="container mt-5">
    <h1 class="text-center">ltl.link</h1>

    <!-- Форма создания новой ссылки -->
    <div class="mb-4">
      <form method="POST" action="dashboard.php">
        <div class="mb-3">
          <label for="long_url" class="form-label">URL</label>
          <input type="text" class="form-control" id="long_url" name="long_url" placeholder="Введите ссылку" required>
        </div>
        <div class="mb-3">
          <label for="title" class="form-label">Название</label>
          <input type="text" class="form-control" id="title" name="title" placeholder="Введите название">
        </div>
        <div class="mb-3">
          <label for="comment" class="form-label">Комментарий</label>
          <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Введите комментарий"></textarea>
        </div>
        <div class="mb-3">
          <label for="expiration_date" class="form-label">Дата истечения срока</label>
          <input type="datetime-local" class="form-control" id="expiration_date" name="expiration_date">
        </div>
        <div class="mb-3">
          <label for="link_password" class="form-label">Пароль</label>
          <input type="password" class="form-control" id="link_password" name="link_password" placeholder="Пароль (необязательно)">
        </div>
        <button type="submit" class="btn btn-primary">Создать</button>
      </form>
    </div>

     <!-- User Links Section -->
      <h2 class="mb-4">Ваши ссылки</h2>
      <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-body">
                <div class="d-flex">
                  <div class="me-3">
                    <img src="qrcodes/<?php echo $row['id']; ?>.png" alt="QR-код" class="img-fluid" style="width: 100px; height: 100px;">
                  </div>
                  <div>
                    <h5 class="card-title"><?php echo $row['title']; ?></h5>
                    <p class="card-text"><strong>Короткая ссылка:</strong> <a href="http://h406470147.nichost.ru/<?php echo $row['short_url']; ?>" target="_blank"><?php echo $row['short_url']; ?></a></p>
                    <p class="card-text"><strong>Длинная ссылка:</strong> <a href="<?php echo $row['long_url']; ?>" target="_blank"><?php echo $row['long_url']; ?></a></p>
                    <p class="card-text"><strong>Комментарий:</strong> <?php echo $row['comment']; ?></p>
                    <?php if ($row['expiration_date']): ?>
                      <p class="card-text"><strong>Срок действия:</strong> <?php echo $row['expiration_date']; ?></p>
                    <?php endif; ?>
                    <p class="card-text"><strong>Переходов по короткой ссылке:</strong> <?php echo $row['short_count']; ?></p>
                    <p class="card-text"><strong>Переходов по QR-коду:</strong> <?php echo $row['qr_count']; ?></p>
                  </div>
                </div>
                <div class="mt-3">
                  <a href="dashboard.php?delete=<?php echo $row['id']; ?>&short_url=<?php echo $row['short_url']; ?>" class="btn btn-danger">Удалить</a>
                  <button class="btn btn-secondary" onclick="showEditForm(<?php echo $row['id']; ?>, '<?php echo $row['long_url']; ?>', '<?php echo $row['short_url']; ?>')">Редактировать</button>
                </div>
                <div id="edit-form-<?php echo $row['id']; ?>" style="display: none;">
                  <form method="POST" action="dashboard.php">
                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                    <div class="mb-3">
                      <label for="new_long_url" class="form-label">Новая длинная ссылка</label>
                      <input type="text" class="form-control" name="new_long_url" value="<?php echo $row['long_url']; ?>">
                    </div>
                    <div class="mb-3">
                      <label for="new_short_url" class="form-label">Новая короткая ссылка</label>
                      <input type="text" class="form-control" name="new_short_url" value="<?php echo $row['short_url']; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

  <script>
    function showEditForm(id, longUrl, shortUrl) {
      const editForm = document.getElementById('edit-form-' + id);
      if (editForm.style.display === "none") {
        editForm.style.display = "block";
      } else {
        editForm.style.display = "none";
      }
    }
  </script>
</body>
</html>

<?php
if ($conn) {
    $conn->close();
}
?>
