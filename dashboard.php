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
    $new_title = $_POST['new_title'];
    $new_comment = $_POST['new_comment'];
    $new_expiration_date = $_POST['new_expiration_date'] ? $_POST['new_expiration_date'] : null;
    $new_password = $_POST['new_password'] ? $_POST['new_password'] : null;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM four WHERE short_url = ? AND id != ?");
    $stmt->bind_param("si", $new_short_url, $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "Ошибка: Короткая ссылка уже существует.";
    } else {
        $stmt = $conn->prepare("UPDATE four SET long_url = ?, short_url = ?, title = ?, comment = ?, expiration_date = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $new_long_url, $new_short_url, $new_title, $new_comment, $new_expiration_date, $new_password, $id);
        
        if ($stmt->execute()) {
            require_once 'phpqrcode/qrlib.php';
            $path = "qrcodes/".$new_short_url.".png";
            QRcode::png('http://h406470147.nichost.ru/'.$new_short_url, $path);
            header('Location: dashboard.php');
            exit;
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $long_url = $_POST['long_url'];
    
    $utm_source = $_POST['utm_source'] ?? null;
    $utm_medium = $_POST['utm_medium'] ?? null;
    $utm_campaign = $_POST['utm_campaign'] ?? null;
    $utm_term = $_POST['utm_term'] ?? null;
    $utm_content = $_POST['utm_content'] ?? null;
    // Build UTM query string
    $utm_params = [];
    if ($utm_source) $utm_params[] = "utm_source=$utm_source";
    if ($utm_medium) $utm_params[] = "utm_medium=$utm_medium";
    if ($utm_campaign) $utm_params[] = "utm_campaign=$utm_campaign";
    if ($utm_term) $utm_params[] = "utm_term=$utm_term";
    if ($utm_content) $utm_params[] = "utm_content=$utm_content";
    if (!empty($utm_params)) {
        $long_url .= (strpos($long_url, '?') === false ? '?' : '&') . implode('&', $utm_params);
    }
    
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
      background-color: #0c6efc; /* Жёлтый цвет меню */
    }
    .user-icon {
      width: 40px;
      height: 40px;
      background-color: #0c6efc;
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
          <li><a class="dropdown-item" href="settings.php">Settings</a></li>
          <li><a class="dropdown-item" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>
  
<div class="container mt-5">

    <!-- Форма создания новой ссылки -->
    <div class="mb-4">
    <form method="POST" action="dashboard.php" class="d-flex">
    <input type="text" class="form-control me-2" id="long_url" name="long_url" placeholder="Введите ссылку" required>
    <button type="submit" class="btn btn-primary">Create</button>
    <!-- </form> -->
    </div>

        <!-- Тумблеры вызова доп.настроек -->
        <div class="mb-3 d-flex align-items-center">
            
        <!-- Toggle for UTM fields -->
        <div class="form-check form-switch me-3">
        <input class="form-check-input" type="checkbox" id="toggleUTMs" onchange="toggleUTMFields()">
        <label class="form-check-label" for="toggleUTMs">Add UTMs</label>
        </div>
            
        <div class="form-check form-switch me-3">
        <input class="form-check-input" type="checkbox" id="toggleTitle" onchange="toggleField('title')">
        <label class="form-check-label" for="toggleTitle">Добавить название</label>
        </div>
        <div class="form-check form-switch me-3">
        <input class="form-check-input" type="checkbox" id="toggleExpiration" onchange="toggleField('expiration')">
        <label class="form-check-label" for="toggleExpiration">Дата истечения срока</label>
        </div>
        <div class="form-check form-switch me-3">
        <input class="form-check-input" type="checkbox" id="togglePassword" onchange="toggleField('password')">
        <label class="form-check-label" for="togglePassword">Пароль</label>
        </div>
        <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="toggleComment" onchange="toggleField('comment')">
        <label class="form-check-label" for="toggleComment">Комментарий</label>
        </div>
        </div>
        
        <!-- UTM fields (hidden by default) -->
        <div id="utmFields" style="display: none;">
        <div class="mb-3">
        <label for="utm_source" class="form-label">UTM Source</label>
        <input type="text" class="form-control" id="utm_source" name="utm_source" placeholder="Enter UTM source">
        </div>
        <div class="mb-3">
        <label for="utm_medium" class="form-label">UTM Medium</label>
        <input type="text" class="form-control" id="utm_medium" name="utm_medium" placeholder="Enter UTM medium">
        </div>
        <div class="mb-3">
        <label for="utm_campaign" class="form-label">UTM Campaign</label>
        <input type="text" class="form-control" id="utm_campaign" name="utm_campaign" placeholder="Enter UTM campaign">
        </div>
        <div class="mb-3">
        <label for="utm_term" class="form-label">UTM Term</label>
        <input type="text" class="form-control" id="utm_term" name="utm_term" placeholder="Enter UTM term">
        </div>
        <div class="mb-3">
        <label for="utm_content" class="form-label">UTM Content</label>
        <input type="text" class="form-control" id="utm_content" name="utm_content" placeholder="Enter UTM content">
        </div>
        </div>
        
        <!-- Остальные поля (hidden by default) -->
        <div id="titleField" class="mb-3" style="display:none;">
        <label for="title" class="form-label">Название</label>
        <input type="text" class="form-control" id="title" name="title" placeholder="Введите название">
        </div>
        <div id="expirationField" class="mb-3" style="display:none;">
        <label for="expiration_date" class="form-label">Дата истечения срока</label>
        <input type="datetime-local" class="form-control" id="expiration_date" name="expiration_date">
        </div>
        <div id="passwordField" class="mb-3" style="display:none;">
        <label for="link_password" class="form-label">Пароль</label>
        <input type="password" class="form-control" id="link_password" name="link_password" placeholder="Введите пароль">
        </div>
        <div id="commentField" class="mb-3" style="display:none;">
        <label for="comment" class="form-label">Комментарий</label>
        <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Введите комментарий"></textarea>
        </div>


        </form>
        </div>

     <!-- User Links Section -->
      
      <div class="container">
          
      <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-12">
        <div class="card mb-3">
        <div class="card-body">
        <form method="POST" action="dashboard.php" id="edit-form-<?php echo $row['id']; ?>">
        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">

        <!-- показываем QR-код -->
        <p class="card-text">
        <img src="qrcodes/<?php echo $row['id']; ?>.png" alt="QR-код" class="img-fluid" style="width: 100px; height: 100px;">
        </p>
          
          <!-- Название -->
          <h5 class="card-title">
            <strong>Название:</strong> 
            <span id="title-text-<?php echo $row['id']; ?>"><?php echo $row['title']; ?></span>
            <input type="text" name="new_title" id="title-input-<?php echo $row['id']; ?>" value="<?php echo $row['title']; ?>" style="display: none;">
          </h5>
          
        <!-- Дата создания -->
        <p class="card-text" style="color: gray;">
        <strong>Дата создания:</strong> <?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?>
        </p>

          <!-- Короткая ссылка -->
          <p class="card-text">
            <strong>Короткая ссылка:</strong> 
            <span id="short-url-text-<?php echo $row['id']; ?>"><a href="http://h406470147.nichost.ru/<?php echo $row['short_url']; ?>" target="_blank"><?php echo $row['short_url']; ?></a></span>
            <input type="text" name="new_short_url" id="short-url-input-<?php echo $row['id']; ?>" value="<?php echo $row['short_url']; ?>" style="display: none;">
          </p>

          <!-- Длинная ссылка -->
          <p class="card-text">
            <strong>Длинная ссылка:</strong> 
            <span id="long-url-text-<?php echo $row['id']; ?>"><a href="<?php echo $row['long_url']; ?>" target="_blank"><?php echo $row['long_url']; ?></a></span>
            <input type="text" name="new_long_url" id="long-url-input-<?php echo $row['id']; ?>" value="<?php echo $row['long_url']; ?>" style="display: none;">
          </p>

          <!-- Комментарий -->
          <p class="card-text">
            <strong>Комментарий:</strong> 
            <span id="comment-text-<?php echo $row['id']; ?>"><?php echo $row['comment']; ?></span>
            <textarea name="new_comment" id="comment-input-<?php echo $row['id']; ?>" style="display: none;"><?php echo $row['comment']; ?></textarea>
          </p>

          <!-- Срок действия -->
        <p class="card-text">
        <strong>Срок действия:</strong> 
        <span id="expiration-text-<?php echo $row['id']; ?>"><?php echo $row['expiration_date'] ? $row['expiration_date'] : 'Не установлен'; ?></span>
        <input type="datetime-local" name="new_expiration_date" id="expiration-input-<?php echo $row['id']; ?>" value="<?php echo $row['expiration_date'] ? $row['expiration_date'] : ''; ?>" style="display: none;">
        </p>

          <!-- Пароль -->
          <p class="card-text">
            <strong>Пароль:</strong> 
            <span id="password-text-<?php echo $row['id']; ?>"><?php echo $row['password'] ? '*****' : 'Нет'; ?></span>
            <input type="password" name="new_password" id="password-input-<?php echo $row['id']; ?>" value="<?php echo $row['password']; ?>" style="display: none;">
          </p>

          <!-- Переходы -->
          <p class="card-text"><strong>Переходов по короткой ссылке:</strong> <?php echo $row['short_count']; ?></p>
          <p class="card-text"><strong>Переходов по QR-коду:</strong> <?php echo $row['qr_count']; ?></p>

            <div class="mt-3">
            <button type="button" class="btn btn-secondary" id="edit-btn-<?php echo $row['id']; ?>" onclick="toggleEdit(<?php echo $row['id']; ?>)">Редактировать</button>
            <button type="submit" class="btn btn-primary" id="save-btn-<?php echo $row['id']; ?>" style="display: none;">Сохранить</button>
            <button type="button" class="btn btn-warning" id="cancel-btn-<?php echo $row['id']; ?>" style="display: none;" onclick="cancelEdit(<?php echo $row['id']; ?>)">Отмена</button>
            <a href="?delete=<?php echo $row['id']; ?>&short_url=<?php echo $row['short_url']; ?>" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту ссылку?')">Удалить</a>
            </div>
  </form>
</div>


    </div>
  </div>
<?php endwhile; ?>

      </div>
    </div>
    </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

<!-- Скрипт для включения полей рубильником при генерации ссылки
<script>
function toggleEdit(id) {
  const elements = [
    {text: 'title-text', input: 'title-input'},
    {text: 'short-url-text', input: 'short-url-input'},
    {text: 'long-url-text', input: 'long-url-input'},
    {text: 'comment-text', input: 'comment-input'},
    {text: 'expiration-text', input: 'expiration-input'},
    {text: 'password-text', input: 'password-input'}
  ];

  elements.forEach(el => {
    const textEl = document.getElementById(`${el.text}-${id}`);
    const inputEl = document.getElementById(`${el.input}-${id}`);
    
    if (textEl && inputEl) {
      textEl.style.display = textEl.style.display === 'none' ? 'block' : 'none';
      inputEl.style.display = inputEl.style.display === 'none' ? 'block' : 'none';
    }
  });

  // Toggle buttons
  document.getElementById('edit-btn-' + id).style.display = 
      document.getElementById('edit-btn-' + id).style.display === 'none' ? 'block' : 'none';
  document.getElementById('save-btn-' + id).style.display = 
      document.getElementById('save-btn-' + id).style.display === 'none' ? 'block' : 'none';
  document.getElementById('cancel-btn-' + id).style.display = 
      document.getElementById('cancel-btn-' + id).style.display === 'none' ? 'block' : 'none';
}
</script>
 -->
 
<!-- включает только название и коммент, остальные поля рубильником не вызываются почему-то
    <script>
    function toggleField(fieldName) {
    const field = document.getElementById(fieldName + 'Field');
    field.style.display = field.style.display === 'none' ? 'block' : 'none';
    }
    </script>
     --> 

<!-- Скрипт для включения полей рубильником при генерации ссылки
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
 -->
 
<!-- РАБОЧИЙ скрипт для включения/отключения utm-полей при переключении рубильника -->
<script>
function toggleUTMFields() {
  const utmFields = document.getElementById('utmFields');
  utmFields.style.display = utmFields.style.display === 'none' ? 'block' : 'none';
}
</script>

<!-- Скрипт для включения/отключения остальных полей при переключении рубильников -->
<script>
  function toggleField(fieldId) {
    const field = document.getElementById(fieldId + 'Field');
    field.style.display = field.style.display === 'none' ? 'block' : 'none';
  }
</script>

<!-- РАБОЧИЙ скрипт редактирования карточки ссылок -->
<script>
  function toggleEdit(id) {
    const titleText = document.getElementById('title-text-' + id);
    const titleInput = document.getElementById('title-input-' + id);
    const shortUrlText = document.getElementById('short-url-text-' + id);
    const shortUrlInput = document.getElementById('short-url-input-' + id);
    const longUrlText = document.getElementById('long-url-text-' + id);
    const longUrlInput = document.getElementById('long-url-input-' + id);
    const commentText = document.getElementById('comment-text-' + id);
    const commentInput = document.getElementById('comment-input-' + id);
    const expirationText = document.getElementById('expiration-text-' + id);
    const expirationInput = document.getElementById('expiration-input-' + id);
    const passwordText = document.getElementById('password-text-' + id);
    const passwordInput = document.getElementById('password-input-' + id);
    const editButton = document.getElementById('edit-btn-' + id);
    const saveButton = document.getElementById('save-btn-' + id);
    const cancelButton = document.getElementById('cancel-btn-' + id);

    // Переключение видимости полей редактирования
    titleText.style.display = titleText.style.display === 'none' ? 'block' : 'none';
    titleInput.style.display = titleInput.style.display === 'none' ? 'block' : 'none';
    shortUrlText.style.display = shortUrlText.style.display === 'none' ? 'block' : 'none';
    shortUrlInput.style.display = shortUrlInput.style.display === 'none' ? 'block' : 'none';
    longUrlText.style.display = longUrlText.style.display === 'none' ? 'block' : 'none';
    longUrlInput.style.display = longUrlInput.style.display === 'none' ? 'block' : 'none';
    commentText.style.display = commentText.style.display === 'none' ? 'block' : 'none';
    commentInput.style.display = commentInput.style.display === 'none' ? 'block' : 'none';
    
    if (expirationText && expirationInput) {
      expirationText.style.display = expirationText.style.display === 'none' ? 'block' : 'none';
      expirationInput.style.display = expirationInput.style.display === 'none' ? 'block' : 'none';
    }

    passwordText.style.display = passwordText.style.display === 'none' ? 'block' : 'none';
    passwordInput.style.display = passwordInput.style.display === 'none' ? 'block' : 'none';

    editButton.style.display = editButton.style.display === 'none' ? 'block' : 'none';
    saveButton.style.display = saveButton.style.display === 'none' ? 'block' : 'none';
    cancelButton.style.display = cancelButton.style.display === 'none' ? 'block' : 'none';
  }

  function cancelEdit(id) {
    toggleEdit(id); // Скрываем поля редактирования
  }
</script>

</body>
</html>

<?php
if ($conn) {
    $conn->close();
}
?>
