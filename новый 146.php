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
    logMessage("Database connection failed: " . $conn->connect_error, 'ERROR');
    die("Connection failed: " . $conn->connect_error);
} else {
    logMessage("Database connected successfully.", 'SUCCESS');
}

// Deleting expired links
$conn->query("DELETE FROM four WHERE expiration_date IS NOT NULL AND expiration_date <= NOW()");

if (isset($_GET['delete'])) {
    // Sanitize the input
    $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    $short_url = filter_var($_GET['short_url'], FILTER_SANITIZE_STRING);

    // Log the deletion request
    logMessage("Received delete request for short URL with ID: $id and short URL: $short_url", 'INFO');

    // Prepare and execute the SQL DELETE statement
    $stmt = $conn->prepare("DELETE FROM four WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    // Log the SQL query execution
    logMessage("Executing SQL query: DELETE FROM four WHERE id = $id", 'INFO');

    if ($stmt->execute()) {
        logMessage("Short URL with ID: $id deleted successfully from the database", 'SUCCESS');

        // Attempt to delete the QR code file
        $qrCodeFile = "qrcodes/$id.png";
        if (file_exists($qrCodeFile)) {
            if (unlink($qrCodeFile)) {
                logMessage("QR code file for short URL with ID: $id deleted successfully", 'SUCCESS');
            } else {
                logMessage("Failed to delete QR code file for short URL with ID: $id", 'ERROR');
            }
        } else {
            logMessage("QR code file for short URL with ID: $id does not exist", 'WARNING');
        }

        // Redirect after successful deletion
        header("Location: dashboard_new.php");
        exit();
    } else {
        logMessage("Error executing SQL delete: " . $stmt->error, 'ERROR');
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    // Capture and sanitize inputs
    $id = filter_var($_POST['edit_id'], FILTER_VALIDATE_INT);
    $new_long_url = filter_var($_POST['new_long_url'], FILTER_SANITIZE_URL);
    $new_short_url = filter_var($_POST['new_short_url'], FILTER_SANITIZE_STRING);
    $new_title = filter_var($_POST['new_title'], FILTER_SANITIZE_STRING);
    $new_expiration_date = !empty($_POST['new_expiration_date']) ? $_POST['new_expiration_date'] : null;
    $new_password = !empty($_POST['new_password']) ? password_hash($_POST['new_password'], PASSWORD_BCRYPT) : null; // Hash new password if provided

    // Log the incoming data
    logMessage("Attempting to edit short URL with ID: $id. New values - Long URL: $new_long_url, Short URL: $new_short_url, Title: $new_title", 'INFO');

    // Check if the short URL already exists and belongs to a different record
    $stmt = $conn->prepare("SELECT COUNT(*) FROM four WHERE short_url = ? AND id != ?");
    $stmt->bind_param("si", $new_short_url, $id);
    
    // Log the SQL query execution
    logMessage("Executing SQL query: SELECT COUNT(*) FROM four WHERE short_url = $new_short_url AND id != $id", 'INFO');

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        logMessage("Error: Short URL $new_short_url already exists.", 'ERROR');
        echo "Error: Short URL already exists.";
    } else {
        // Update the record with the new data
        $stmt = $conn->prepare("UPDATE four SET long_url = ?, short_url = ?, title = ?, expiration_date = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $new_long_url, $new_short_url, $new_title, $new_expiration_date, $new_password, $id);
        
        // Log the SQL query execution
        logMessage("Executing SQL query: UPDATE four SET long_url = $new_long_url, short_url = $new_short_url, title = $new_title WHERE id = $id", 'INFO');

        if ($stmt->execute()) {
            logMessage("Short URL with ID: $id updated successfully", 'SUCCESS');
            
            // Regenerate QR code
            require_once 'phpqrcode/qrlib.php';
            $path = "qrcodes/".$new_short_url.".png";
            QRcode::png('http://h406470147.nichost.ru/'.$new_short_url, $path);
            logMessage("QR code regenerated for short URL: $new_short_url", 'SUCCESS');
            
            // Redirect after successful update
            header('Location: dashboard_new.php');
            exit();
        } else {
            logMessage("Error executing SQL update: " . $stmt->error, 'ERROR');
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    
    logMessage("POST request received for URL creation", 'INFO');  // Add this line
    
    // Capture and sanitize the long URL
    $long_url = filter_var($_POST['long_url'], FILTER_SANITIZE_URL);
    
    // Capture UTM parameters with sanitization
    $utm_source = filter_var($_POST['utm_source'] ?? null, FILTER_SANITIZE_STRING);
    $utm_medium = filter_var($_POST['utm_medium'] ?? null, FILTER_SANITIZE_STRING);
    $utm_campaign = filter_var($_POST['utm_campaign'] ?? null, FILTER_SANITIZE_STRING);
    $utm_term = filter_var($_POST['utm_term'] ?? null, FILTER_SANITIZE_STRING);
    $utm_content = filter_var($_POST['utm_content'] ?? null, FILTER_SANITIZE_STRING);
    
    // Build UTM query string
    $utm_params = [];
    if ($utm_source) $utm_params[] = "utm_source=$utm_source";
    if ($utm_medium) $utm_params[] = "utm_medium=$utm_medium";
    if ($utm_campaign) $utm_params[] = "utm_campaign=$utm_campaign";
    if ($utm_term) $utm_params[] = "utm_term=$utm_term";
    if ($utm_content) $utm_params[] = "utm_content=$utm_content";
    
    // Append UTM parameters to the long URL if any exist
    if (!empty($utm_params)) {
        $long_url .= (strpos($long_url, '?') === false ? '?' : '&') . implode('&', $utm_params);
    }
    
    // Log the UTM parameters and URL construction process
    logMessage("Constructed long URL: $long_url with UTM parameters: " . implode(', ', $utm_params), 'INFO');
    
    // Generate short URL
    $short_url = shortenUrl();
    logMessage("Generated short URL: $short_url", 'INFO');
    
    // Capture other form fields with sanitization
    $title = filter_var($_POST['title'] ?? 'Untitled', FILTER_SANITIZE_STRING); // this doesn't work - if title is null it doesn't get replaced with Untitled
    $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null;
    $link_password = !empty($_POST['link_password']) ? password_hash($_POST['link_password'], PASSWORD_BCRYPT) : null; // Hash the password if provided

    // Prepare the SQL insert statement
    $stmt = $conn->prepare("INSERT INTO four (user_id, long_url, short_url, title, expiration_date, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $_SESSION['user_id'], $long_url, $short_url, $title, $expiration_date, $link_password);

    // Log the SQL query details
    logMessage("Executing SQL query: INSERT INTO four (user_id, long_url, short_url, title, expiration_date, password)", 'INFO');

    // Execute the query and handle success or failure
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        logMessage("Short URL inserted successfully with ID: $id", 'SUCCESS');

        // Generate QR code
        require_once 'phpqrcode/qrlib.php';
        QRcode::png("http://h406470147.nichost.ru/r.php?id=$id", "qrcodes/$id.png");
        logMessage("QR code generated for short URL: $short_url with ID: $id", 'SUCCESS');
    } else {
        // Log SQL error
        logMessage("Error executing SQL query: " . $stmt->error, 'ERROR');
        echo "Error: " . $stmt->error;
    }

    // Close the prepared statement
    $stmt->close();
}

// Old code for retrieving results
// $user_id = $_SESSION['user_id'];
//$result = $conn->query("SELECT * FROM four WHERE user_id = $user_id ORDER BY created_at DESC");

// Retrieve all URLs for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM four WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);

logMessage("Executing SQL query to retrieve URLs for user ID: $user_id", 'INFO');

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    logMessage("Fetched " . $result->num_rows . " URLs for user ID: $user_id", 'SUCCESS');
} else {
    logMessage("No URLs found for user ID: $user_id", 'INFO');
}

$stmt->close();


?>

    <!doctype html>
    <html lang="ru">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/style.css"> <!-- Link to your CSS file -->
        <title>Dashboard</title>
    </head>

    <body>
        <!-- Menu -->
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
        
        
        <!-- Flex container for the form and the links -->
        <div class="d-flex" style="height: 100vh; overflow: hidden;">
        
            <!-- New short url builder form -->
            <div class="builder-form" style="flex: 0 0 50%; padding: 20px; box-sizing: border-box; overflow-y: auto;">
                <div class="container">
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="mb-4">
                                    <form method="POST" action="dashboard_new.php">
                                        
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="long_url" name="long_url" placeholder="Enter your link" required>
                                            <button type="submit" class="btn btn-primary">Create</button>
                                        </div>
                                        
                                        <!-- Tumblers for additional options -->
                                        <div class="mb-3 d-flex align-items-center">
                            
                                            <!-- Toggle for UTM fields -->
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" id="toggleUTMs" onchange="toggleUTMFields()">
                                                <label class="form-check-label" for="toggleUTMs">Add UTMs</label>
                                            </div>
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" id="toggleExpiration" onchange="toggleField('expiration')">
                                                <label class="form-check-label" for="toggleExpiration">Set Expiration Date</label>
                                            </div>
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" id="togglePassword" onchange="toggleField('password')">
                                                <label class="form-check-label" for="togglePassword">Set Password</label>
                                            </div>
                                            
                                            <?php
                                            /*
                                            ?>
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" id="toggleTitle" onchange="toggleField('title')">
                                                <label class="form-check-label" for="toggleTitle">Add Title</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="toggleComment" onchange="toggleField('comment')">
                                                <label class="form-check-label" for="toggleComment">Add Comment</label>
                                            </div>
                                            <?php
                                            */
                                            ?>
                                        </div>
                    
                                        <!-- UTM fields (hidden by default) -->
                                        <div id="utmFields" style="display: none;">
                                            <div class="mb-3">
                                                <label for="utm_source" class="form-label">UTM Source</label>
                                                <input type="text" class="form-control" id="utm_source" name="utm_source" placeholder="Enter UTM source">
                                            <!-- </div>
                                            <div class="mb-3"> -->
                                                <label for="utm_medium" class="form-label">UTM Medium</label>
                                                <input type="text" class="form-control" id="utm_medium" name="utm_medium" placeholder="Enter UTM medium">
                                            <!-- </div>
                                            <div class="mb-3"> -->
                                                <label for="utm_campaign" class="form-label">UTM Campaign</label>
                                                <input type="text" class="form-control" id="utm_campaign" name="utm_campaign" placeholder="Enter UTM campaign">
                                            <!-- </div>
                                            <div class="mb-3"> -->
                                                <label for="utm_term" class="form-label">UTM Term</label>
                                                <input type="text" class="form-control" id="utm_term" name="utm_term" placeholder="Enter UTM term">
                                            <!-- </div>
                                            <div class="mb-3"> -->
                                                <label for="utm_content" class="form-label">UTM Content</label>
                                                <input type="text" class="form-control" id="utm_content" name="utm_content" placeholder="Enter UTM content">
                                            </div>
                                        </div>
                    
                                        <!-- Остальные поля (hidden by default) -->
                                        <div id="titleField" class="mb-3" style="display:none;">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Your Title">
                                        </div>
                                        <div id="expirationField" class="mb-3" style="display:none;">
                                            <label for="expiration_date" class="form-label">Expiration date</label>
                                            <input type="datetime-local" class="form-control" id="expiration_date" name="expiration_date">
                                        </div>
                                        <div id="passwordField" class="mb-3" style="display:none;">
                                            <label for="link_password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="link_password" name="link_password" placeholder="Your Password">
                                        </div>
                                        <?php
                                        /*
                                        ?>
                                        <div id="commentField" class="mb-3" style="display:none;">
                                            <label for="comment" class="form-label">Comment</label>
                                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Your Comment"></textarea>
                                        </div>
                                        <?php
                                        */
                                        ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Links Cards -->
            <div class="links-cards" style="flex: 1; overflow-y: auto; padding: 20px; box-sizing: border-box;">
                <div class="container">
                    <div class="row">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <form method="POST" action="dashboard_new.php" id="edit-form-<?php echo $row['id']; ?>">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
        
                                            <!-- Контейнер для кнопок -->
                                            <div class="card-buttons">
                                                <button type="button" class="btn btn-secondary" id="edit-btn-<?php echo $row['id']; ?>" onclick="toggleEdit(<?php echo $row['id']; ?>)">Edit</button>
                                                <button type="submit" class="btn btn-primary" id="save-btn-<?php echo $row['id']; ?>" style="display: none;">Save</button>
                                                <button type="button" class="btn btn-warning" id="cancel-btn-<?php echo $row['id']; ?>" style="display: none;" onclick="cancelEdit(<?php echo $row['id']; ?>)">Cancel</button>
                                                <a href="?delete=<?php echo $row['id']; ?>&short_url=<?php echo $row['short_url']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this link?')">Delete</a>
                                            </div>
        
                                            <!-- Контент карточки -->
                                            <p class="card-text">
                                                <img src="qrcodes/<?php echo $row['id']; ?>.png" alt="QR-код" class="img-fluid" style="width: 100px; height: 100px;">
                                            </p>
                                            <h5 class="card-title">
                                                <!-- <strong>Title:</strong> -->
                                                <span id="title-text-<?php echo $row['id']; ?>"><?php echo $row['title'] ? $row['title'] : 'Untitled'; ?></span>
                                                <input type="text" name="new_title" id="title-input-<?php echo $row['id']; ?>" value="<?php echo $row['title']; ?>" style="display: none;">
                                            </h5>
                                            <!-- Короткая ссылка -->
                                            <p class="card-text">
                                                <!-- <strong>Shortened URL:</strong> -->
                                                <span id="short-url-text-<?php echo $row['id']; ?>"><a href="http://h406470147.nichost.ru/<?php echo $row['short_url']; ?>" target="_blank">http://h406470147.nichost.ru/<?php echo $row['short_url']; ?></a></span>
                                                <input type="text" name="new_short_url" id="short-url-input-<?php echo $row['id']; ?>" value="<?php echo $row['short_url']; ?>" style="display: none;">
                                            </p>
        
                                            <!-- Длинная ссылка -->
                                            <p class="card-text">
                                                <!-- <strong>Original URL:</strong> -->
                                                <span id="long-url-text-<?php echo $row['id']; ?>"><a href="<?php echo $row['long_url']; ?>" target="_blank"><?php echo $row['long_url']; ?></a></span>
                                                <input type="text" name="new_long_url" id="long-url-input-<?php echo $row['id']; ?>" value="<?php echo $row['long_url']; ?>" style="display: none;">
                                            </p>
                                            
                                            <?php
                                            // Start PHP block to comment out HTML+PHP code
                                            
                                            /*
                                            ?>
                                            <!-- Комментарий -->
                                            <p class="card-text">
                                                <!-- <strong>Comment:</strong> -->
                                                <span id="comment-text-<?php echo $row['id']; ?>"><?php echo $row['comment'] ? $row['comment'] : 'Not set'; ?></span>
                                                <textarea name="new_comment" id="comment-input-<?php echo $row['id']; ?>" style="display: none;">
                                                    <?php echo $row['comment']; ?>
                                                </textarea>
                                            </p>
                                            
                                            
                                            <!-- Expiration date -->
                                            <p class="card-text">
                                                <strong>Expiration date:</strong>
                                                <span id="expiration-text-<?php echo $row['id']; ?>"><?php echo $row['expiration_date'] ? $row['expiration_date'] : 'Not set'; ?></span>
                                                <input type="datetime-local" name="new_expiration_date" id="expiration-input-<?php echo $row['id']; ?>" value="<?php echo $row['expiration_date'] ? $row['expiration_date'] : ''; ?>" style="display: none;">
                                            </p>
                                            
        
                                            <!-- Password -->
                                            <p class="card-text">
                                                <strong>Password:</strong>
                                                <span id="password-text-<?php echo $row['id']; ?>"><?php echo $row['password'] ? '*****' : 'Not set'; ?></span>
                                                <input type="password" name="new_password" id="password-input-<?php echo $row['id']; ?>" value="<?php echo $row['password']; ?>" style="display: none;">
                                            </p>
                                            <?php
                                            */
                                            ?>
                                            
                                            <p class="card-text d-flex align-items-center">
                                                <!-- Expiration date -->
                                                <?php if (!empty($row['expiration_date'])): ?>
                                                    <span class="expiration-text-<?php echo $row['id']; ?> me-4">
                                                        <strong>Expires at:</strong> <?php echo $row['expiration_date']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <a href="#" class="text-muted expiration-link me-4" onclick="toggleEdit(<?php echo $row['id']; ?>)">
                                                        add expiration date
                                                    </a>
                                                <?php endif; ?>
                                            
                                                <!-- Password -->
                                                <?php if (!empty($row['password'])): ?>
                                                    <a href="#" class="text-primary password-link" onclick="toggleEdit(<?php echo $row['id']; ?>)">
                                                        change password
                                                    </a>
                                                <?php else: ?>
                                                    <a href="#" class="text-muted password-link" onclick="toggleEdit(<?php echo $row['id']; ?>)">
                                                        set password
                                                    </a>
                                                <?php endif; ?>
                                            </p>
                                            
                                            <?php
                                            /*
                                            ?>
                                            <!-- Переходы -->
                                            <p class="card-text"><strong>Short URL hits</strong>
                                                <?php echo $row['short_count']; ?>
                                            </p>
                                            <p class="card-text"><strong>QR code hits:</strong>
                                                <?php echo $row['qr_count']; ?>
                                            </p>
                                            <?php
                                            */
                                            ?>
                                        </form>
                                    </div>
        
                                    <!-- Дата создания -->
                                    <div class="card-footer">
                                        <p class="card-text" style="color: gray;">
                                            <strong>Created:</strong>
                                            <?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?>
                                             | <strong>Short URL hits:</strong>
                                                <?php echo $row['short_count']; ?>
                                             | <strong>QR code hits:</strong>
                                                <?php echo $row['qr_count']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

        <!-- Script for toggling url utm parameters -->
        <script>
            function toggleUTMFields() {
              const utmFields = document.getElementById('utmFields');
              utmFields.style.display = utmFields.style.display === 'none' ? 'block' : 'none';
            }
        </script>

        <!-- Script for toggling other url parameters -->
        <script>
            function toggleField(fieldId) {
                const field = document.getElementById(fieldId + 'Field');
                field.style.display = field.style.display === 'none' ? 'block' : 'none';
              }
        </script>

        <!-- Script for url card editing -->
        <script>
            function toggleEdit(id) {
                const titleText = document.getElementById('title-text-' + id);
                const titleInput = document.getElementById('title-input-' + id);
                const shortUrlText = document.getElementById('short-url-text-' + id);
                const shortUrlInput = document.getElementById('short-url-input-' + id);
                const longUrlText = document.getElementById('long-url-text-' + id);
                const longUrlInput = document.getElementById('long-url-input-' + id);
                //const commentText = document.getElementById('comment-text-' + id);
                //const commentInput = document.getElementById('comment-input-' + id);
                const expirationText = document.getElementById('expiration-text-' + id);
                const expirationInput = document.getElementById('expiration-input-' + id);
                const passwordText = document.getElementById('password-text-' + id);
                const passwordInput = document.getElementById('password-input-' + id);
                const editButton = document.getElementById('edit-btn-' + id);
                const saveButton = document.getElementById('save-btn-' + id);
                const cancelButton = document.getElementById('cancel-btn-' + id);
            
                // Toogling edit fields visibility
                titleText.style.display = titleText.style.display === 'none' ? 'block' : 'none';
                titleInput.style.display = titleInput.style.display === 'none' ? 'block' : 'none';
                shortUrlText.style.display = shortUrlText.style.display === 'none' ? 'block' : 'none';
                shortUrlInput.style.display = shortUrlInput.style.display === 'none' ? 'block' : 'none';
                longUrlText.style.display = longUrlText.style.display === 'none' ? 'block' : 'none';
                longUrlInput.style.display = longUrlInput.style.display === 'none' ? 'block' : 'none';
                //commentText.style.display = commentText.style.display === 'none' ? 'block' : 'none';
                //commentInput.style.display = commentInput.style.display === 'none' ? 'block' : 'none';
                
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
                toggleEdit(id); // Hiding edit fields
              }
        </script>

    </body>

    </html>

    <?php
if ($conn) {
    $conn->close();
}
?>