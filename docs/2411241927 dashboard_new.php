<?php

//попытка увеличить продолжительность сессии
ini_set('session.gc_maxlifetime', 864000);  // 24 часа
session_set_cookie_params(864000); // срок действия cookie сессии

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
        header("Location: /dashboard");
        exit();
    } else {
        logMessage("Error executing SQL delete: " . $stmt->error, 'ERROR');
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Function to validate the long URL with additional domain checks
function validateLongUrl($url) {
    // If the URL doesn't start with http:// or https://, prepend http://
    if (!preg_match("/^https?:\/\//", $url)) {
        $url = "http://" . $url;
    }

    // Check if the URL is valid using FILTER_VALIDATE_URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        logMessage("Invalid URL: $url", 'ERROR'); // Log the invalid URL 
        return false; // Invalid URL
    }

    // Additional check for valid domain (to avoid links like 'https://lnk')
    $host = parse_url($url, PHP_URL_HOST); // Extract the host part of the URL
    if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $host)) {
        logMessage("Invalid domain in URL: $url", 'ERROR'); // Log invalid domain
        return false; // Invalid domain
    }

    return $url; // Return the URL if everything is valid
}

// SHORT URL EDIT
/*if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    // Capture and sanitize inputs
    $id = filter_var($_POST['edit_id'], FILTER_VALIDATE_INT);
    $new_short_url = filter_var($_POST['new_short_url'], FILTER_SANITIZE_STRING);
    $new_title = filter_var($_POST['new_title'], FILTER_SANITIZE_STRING);
    $new_long_url = filter_var($_POST['new_long_url'], FILTER_SANITIZE_URL);
	
	// Error message variable to store the error
    $error_message = '';
	
	// Function to validate a domain-based URL
	function validateLongUrl($url) {
		// If the URL doesn't start with a scheme, add http://
		if (!preg_match("/^https?:\/\//", $url)) {
			$url = "http://" . $url;
		}

		// Now check if it's a valid URL
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			return false; // Not a valid URL
		}

		// Check if domain has a valid structure (google.com, www.google.com, etc.)
		if (!preg_match('/^https?:\/\/([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,6}(\/[a-zA-Z0-9-._~:\/?#[\]@!$&\'()*+,;%=]*)?$/', $url)) {
			return false; // Invalid domain structure
		}

		return $url; // Return the valid URL with schema
	}
	
	// Validate the new long URL using the new function
	$validated_url = validateLongUrl($new_long_url);

	if (!$validated_url) {
		$error_message = 'Error: The new long URL is not valid.';
		echo "<script>$('#errorModal').modal('show');</script>"; // Show modal with error
		exit();
	}

	$new_long_url = $validated_url; // Use the validated long URL
	
    // Log the incoming data
    logMessage("Attempting to edit short URL with ID: $id. New values - Long URL: $new_long_url, Short URL: $new_short_url, Title: $new_title", 'INFO');

    // Error message variable to store the error
    $error_message = '';

    // Check if long_url belongs to the current site domain to prevent loopbacks
    if (strpos($new_long_url, $site_url) === 0) {
        $error_message = 'Error: Cannot create a short link for a link on your own domain.';
    }

    // Check if short URL is at least 4 characters long, contains only valid characters, and has no spaces
    if (strlen($new_short_url) < 4 || !preg_match('/^[a-zA-Z0-9\-]+$/', $new_short_url)) {
        $error_message = 'Error: Short URL must be at least 4 characters long and contain only letters, numbers, and hyphens.';
    }

    // Check if both short and long URLs are not empty
    if (empty($new_short_url) || empty($new_long_url)) {
        $error_message = 'Error: Short URL and long URL cannot be empty.';
    }

    // If there is an error, show the modal
    if (!empty($error_message)) {
        echo "<script>
            $(document).ready(function() {
                $('#errorModal .modal-body').text('$error_message');
                $('#errorModal').modal('show');
            });
        </script>";
    } else {
        // Check if the short URL already exists and belongs to a different record
        $stmt = $conn->prepare("SELECT COUNT(*) FROM four WHERE short_url = ? AND id != ?");
        $stmt->bind_param("si", $new_short_url, $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            logMessage("Error: Short URL $new_short_url already exists.", 'ERROR');
            echo "<script>
                $(document).ready(function() {
                    $('#errorModal .modal-body').text('Error: Short URL already exists.');
                    $('#errorModal').modal('show');
                });
            </script>";
        } else {
            // Update the record with the new data
            $stmt = $conn->prepare("UPDATE four SET long_url = ?, short_url = ?, title = ? WHERE id = ?");
            $stmt->bind_param("sssi", $new_long_url, $new_short_url, $new_title, $id);
            
            // Log the SQL query execution
            logMessage("Executing SQL query: UPDATE four SET long_url = $new_long_url, short_url = $new_short_url, title = $new_title WHERE id = $id", 'INFO');

            if ($stmt->execute()) {
                logMessage("Short URL with ID: $id updated successfully", 'SUCCESS');
                
                // If AJAX, send a success response
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    echo "Success";
                    exit(); // End script if using AJAX
                }

                // If not using AJAX, redirect
                header('Location: /dashboard');
                exit();
            } else {
                logMessage("Error executing SQL update: " . $stmt->error, 'ERROR');
                echo "<script>
                    $(document).ready(function() {
                        $('#errorModal .modal-body').text('Error: " . $stmt->error . "');
                        $('#errorModal').modal('show');
                    });
                </script>";
            }

            $stmt->close();
        }
    }
}*/

// SHORT URL EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    // Capture and sanitize inputs
    $id = filter_var($_POST['edit_id'], FILTER_VALIDATE_INT);
    $new_short_url = filter_var($_POST['new_short_url'], FILTER_SANITIZE_STRING);
    $new_title = htmlspecialchars(trim($_POST['new_title'])) ?: 'Untitled';
    $new_long_url = filter_var($_POST['new_long_url'], FILTER_SANITIZE_URL);

    // Error message variable to store the error
    $error_message = '';

    // Validate the long URL using the provided validateLongUrl function
	$validated_url = validateLongUrl($new_long_url);
	if (!$validated_url) {
		echo json_encode(['status' => 'error', 'message' => 'Error: Invalid URL format.']);
		exit();
	} elseif (strpos($validated_url, $site_url) === 0) {
		echo json_encode(['status' => 'error', 'message' => 'Error: Cannot create a short link for your own domain.']);
		exit();
	} elseif (strlen($new_short_url) < 4 || !preg_match('/^[a-zA-Z0-9]+$/', $new_short_url)) {
		echo json_encode(['status' => 'error', 'message' => 'Error: Short URL must be at least 4 characters long and contain only letters and numbers.']);
		exit();
	} elseif (shortUrlExists($conn, $new_short_url, $id)) {
		echo json_encode(['status' => 'error', 'message' => 'Error: Short URL already exists.']);
		exit();
	} elseif (longUrlExists($conn, $new_long_url, $id)) {
		echo json_encode(['status' => 'error', 'message' => 'Error: Long URL already exists.']);
		exit();
	} elseif (empty($new_long_url) || empty($new_short_url)) {
		echo json_encode(['status' => 'error', 'message' => 'Error: Long URL and short URL cannot be empty.']);
		exit();
	} elseif (mb_strlen($new_title) > 128) {
		$new_title = mb_substr($new_title, 0, 128);
	}

    // If there's an error, display it in the modal
    if (!empty($error_message)) {
        echo "<script>
            $(document).ready(function() {
                $('#errorModal .modal-body').text('$error_message');
                $('#errorModal').modal('show');
            });
        </script>";
        exit();
    }

    // Update the record in the database if validation passes
    $stmt = $conn->prepare("UPDATE four SET long_url = ?, short_url = ?, title = ? WHERE id = ?");
    $stmt->bind_param("sssi", $new_long_url, $new_short_url, $new_title, $id);

    // Execute and log result
    if ($stmt->execute()) {
        logMessage("Short URL with ID: $id updated successfully", 'SUCCESS');
        
        // If AJAX request, respond with success
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(["status" => "success", "message" => "URL updated successfully."]);
            exit();
        }

        // Redirect if not AJAX
        header('Location: /dashboard');
        exit();
    } else {
        // Display error if update fails
        logMessage("Error executing SQL update: " . $stmt->error, 'ERROR');
        echo "<script>
            $(document).ready(function() {
                $('#errorModal .modal-body').text('Error: Could not update the URL.');
                $('#errorModal').modal('show');
            });
        </script>";
    }

    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id']) && isset($_POST['new_password'])) {
    $id = filter_var($_POST['edit_id'], FILTER_VALIDATE_INT);
    //$new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT); // Хешируем новый пароль
	$new_password = !empty($_POST['new_password']) ? password_hash($_POST['new_password'], PASSWORD_BCRYPT) : null;

    // Обновляем пароль в базе данных
    $stmt = $conn->prepare("UPDATE four SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $id);

    if ($stmt->execute()) {
        echo "Password updated successfully.";
    } else {
        http_response_code(500); // Устанавливаем статус ошибки
        echo "Error updating password: " . $stmt->error;
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id']) && isset($_POST['delete_password'])) {
    $id = filter_var($_POST['edit_id'], FILTER_VALIDATE_INT);

    // Удаляем пароль, устанавливая его в NULL
    $stmt = $conn->prepare("UPDATE four SET password = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Password deleted successfully.";
    } else {
        http_response_code(500); // Устанавливаем статус ошибки
        echo "Error deleting password: " . $stmt->error;
    }

    $stmt->close();
}

// Function to normalize URLs to a consistent format
function normalizeUrl($url) {
    // Remove 'www.' if present and ensure it starts with a scheme (http or https)
    $url = preg_replace('/^www\./', '', $url);

    // Prepend 'http://' if no scheme is provided
    if (!preg_match("/^https?:\/\//", $url)) {
        $url = "http://" . $url;
    }

    // Lowercase the URL to handle case sensitivity
    $url = strtolower($url);

    // Remove trailing slash for consistency
    $url = rtrim($url, '/');

    return $url;
}

// Function to normalize the URL without the scheme
function normalizeUrlWithoutScheme($url) {
    // Remove 'www.' if present and ensure it starts with http or https
    $url = preg_replace('/^https?:\/\//', '', $url);  // Remove http:// or https://
    $url = preg_replace('/^www\./', '', $url);        // Remove 'www.' if present

    // Lowercase the URL to handle case sensitivity
    $url = strtolower($url);

    // Remove trailing slash for consistency
    $url = rtrim($url, '/');

    return $url;
}

// SHORT URL CREATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    header('Content-Type: application/json');  // Specify that the response will be in JSON format

    logMessage("POST request received for URL creation", 'INFO');
    
    // Initialize error message variable
    $error_message = '';

    // Sanitize and validate the long URL
    $long_url = trim($_POST['long_url']);  // Remove extra spaces
    $validated_url = validateLongUrl($long_url);

    // Check if the long URL is valid
    if (!$validated_url) {
        echo json_encode(['status' => 'error', 'message' => 'The long URL is not valid.']);
        exit();  // Stop processing if URL is not valid
    } else {
        $long_url = $validated_url; // Use the validated long URL

		// Normalize both the input URL and the site URL (ignoring the scheme)
		$normalized_long_url = normalizeUrlWithoutScheme($long_url);
		$normalized_site_url = normalizeUrlWithoutScheme($site_url);
		
		// Log both normalized URLs for debugging
		// logMessage("Normalized long URL: $normalized_long_url", 'INFO');
		// logMessage("Normalized site URL: $normalized_site_url", 'INFO');

        // Check if the long URL links to the site's own domain (prevent looping)
        if (strpos($normalized_long_url, $normalized_site_url) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot create a short link for a link on your own domain.']);
            exit();  // Stop processing for internal URLs
        }
    }
	
	// Check if the long URL already has a short URL in the database
	$stmt = $conn->prepare("SELECT short_url, user_id FROM four WHERE long_url = ?");
	$stmt->bind_param("s", $long_url);
	$stmt->execute();
	$stmt->bind_result($existing_short_url, $existing_user_id);
	$stmt->fetch();
	$stmt->close();

	if ($existing_short_url) {
		if ($existing_user_id == $_SESSION['user_id']) {
			// The short URL exists in the current user's account
			$error_message = "This long URL already has a short link in your account. You can find it here: <a href='$site_url/$existing_short_url' target='_blank'>$site_url/$existing_short_url</a>.";
		} else {
			// The short URL exists in another user's account
			$error_message = "A short link for this long URL already exists, created by another user.";
		}
		echo json_encode(['status' => 'error', 'message' => $error_message]);
		exit(); // Stop further processing
	}

    // Generate a short URL
    $short_url = filter_var(shortenUrl(), FILTER_SANITIZE_STRING);

    // Validate the short URL (must be at least 4 characters, alphanumeric)
    if (strlen($short_url) < 4 || !preg_match('/^[a-zA-Z0-9\-]+$/', $short_url)) {
        echo json_encode(['status' => 'error', 'message' => 'Short URL must be at least 4 characters long and contain only letters, numbers, and hyphens.']);
        exit();  // Stop processing for invalid short URL
    }

    // Check for empty fields
    if (empty($short_url) || empty($long_url)) {
        echo json_encode(['status' => 'error', 'message' => 'Short URL and long URL cannot be empty.']);
        exit();  // Stop processing if fields are empty
    }

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

    // Log the constructed URL with UTM parameters
    logMessage("Constructed long URL: $long_url with UTM parameters: " . implode(', ', $utm_params), 'INFO');

    // Continue processing the valid form submission
    $title = fetchTitleFromURL($long_url); 
    $image_url = fetchImageFromURL($long_url); 

    $expiration_date = !empty($_POST['expiration_date']) ? filter_var($_POST['expiration_date'], FILTER_SANITIZE_STRING) : null;
    $link_password = !empty($_POST['link_password']) ? password_hash($_POST['link_password'], PASSWORD_BCRYPT) : null;

    // Prepare the SQL statement for inserting the data
    $stmt = $conn->prepare("INSERT INTO four (user_id, long_url, short_url, title, expiration_date, password) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        error_log("Failed to prepare SQL statement: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement']);
        exit();  // Stop further processing on SQL error
    }
    $stmt->bind_param("isssss", $_SESSION['user_id'], $long_url, $short_url, $title, $expiration_date, $link_password);

    // Execute the query
	if ($stmt->execute()) {
		$id = $stmt->insert_id;
		logMessage("Short URL inserted successfully with ID: $id", 'SUCCESS');

		// Generate a QR code
		require_once 'phpqrcode/qrlib.php';
		QRcode::png($site_url . "/r.php?id=$id", "qrcodes/$id.png", QR_ECLEVEL_L, 20);
		logMessage("QR code generated for short URL: $short_url with ID: $id", 'SUCCESS');

		// Return success response in JSON format with title included
		echo json_encode([
			'status' => 'success',
			'id' => $id,
			'short_url' => $short_url,
			'title' => $title,
			'expiration_date' => $expiration_date,  // Expiration date if available
			//'password' => !empty($password) // True if a password is set, false otherwise
			//'password' => !empty($password) ? true : false,
			'password' => !empty($link_password),

		]);
		exit; // Exit to prevent any further output
	} else {
		echo json_encode(['status' => 'error', 'message' => "Error: " . $stmt->error]);
		exit();  // Stop further processing on SQL error
	}

    // Close the prepared statement
    $stmt->close();
}


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
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"> -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css"> <!-- Link to your CSS file -->
        <title>Dashboard</title>
		<!-- jQuery -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
		
		<script>const siteUrl = '<?php echo $site_url; ?>';</script>
    </head>

    <body>
	
		<!-- Loading Overlay (Initially Hidden) -->
		<div id="loading-overlay-global" style="display:none;">
			<h3>Saving, please wait...</h3>
		</div>
		
		<div class="d-flex" style="align-items: stretch; width: 100%; overflow: hidden;">
			
			<!-- Sidebar -->
			
			<!-- <div class="sidebar border-end" style="flex: 0 0 250px; background-color: #343a40; padding: 20px; height: 100vh;"> -->
			
			<nav id="sidebar">
				<div class="sidebar-header">
					<h3><a href="index.php"><span>lnk.monster</span></a></h3>
				</div>

				<ul class="list-unstyled components">
					
					<li>
						<a href="dashboard"><img src="/img/buttons/links.svg"><span>Dashboard</span></a>
					</li>
					
					<li>
					<?php if ($_SESSION['user_role'] === 'admin'): ?>
					<a href="logs/app_log.txt"><img src="/img/buttons/logs.svg"><span>Logs</span></a>
                    <?php endif; ?>
					</li>
					
					<li>
					<?php if ($_SESSION['user_role'] === 'admin'): ?>
					<a href="addnews.php"><img src="/img/buttons/news.svg"><span>Add news</span></a>
                    <?php endif; ?>
					</li>
					
					<li>
						<a href="/logout"><img src="/img/buttons/logout.svg"><span>Logout</span></a>
					</li>
										
					<li>
						<a id="sidebar-collapse" href="javascript:void(0);" onclick="toggleSidebar()">
							<img id="toggle-icon" src="/img/buttons/navigate_before.svg">
							<span>Toggle Sidebar</span>
						</a>
					</li>
				</ul>
			</nav>
		
        <!-- Flex container for the form and the links -->
		<div class="d-flex" id="dasboard-content">
        
            <!-- New short url builder form -->
            <div class="builder-form">
                <div class="container">
                    <div class="col-md-12">
						
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="mb-4">
									
                                    <form id="create-link-form" method="POST" action="/dashboard" autocomplete="off">
                                        
										<div class="input-group mb-3">
											<input type="text" class="form-control" id="long_url" name="long_url" placeholder="Enter your link" required>
											<button type="submit" class="btn btn-primary">Create</button>
										</div>
                                        
                                        <!-- Tumblers for additional options -->
                                        <div class="mb-3 d-flex align-items-center">
                            
                                            <!-- Toggle for UTM fields -->
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" id="toggleUTMs" onchange="toggleUTMFields()">
                                                <label class="form-check-label" for="toggleUTMs">UTMs</label>
                                            </div>
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" id="toggleExpiration" onchange="toggleField('expiration')">
                                                <label class="form-check-label" for="toggleExpiration">Expiring</label>
                                            </div>
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" id="togglePassword" onchange="toggleField('password')">
                                                <label class="form-check-label" for="togglePassword">Password</label>
                                            </div>
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
                                        <!-- <div id="passwordField" class="mb-3" style="display:none;">
                                            <label for="link_password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="link_password" name="link_password" placeholder="Your Password">
                                        </div> -->
										<div id="passwordField" class="mb-3" style="display:none;">
											<label for="link_password" class="form-label">Password</label>
											<div class="input-group">
												<input type="password" class="form-control" id="link_password" name="link_password" placeholder="Your Password">
												<button class="btn btn-outline-secondary btn-toggle-password-visibility" type="button" id="toggle-link-password" onclick="toggleLinkPasswordVisibility()">
													<img class="create-password-eye-icon button-icon" src="/img/buttons/eye.svg">
												</button>
											</div>
										</div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Links Cards -->
            <div class="links-cards">
                <div class="container">
                    <div class="row">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-body position-relative">
										<div id="edit-overlay-card-<?php echo $row['id']; ?>" class="edit-overlay-card" style="display:none;"></div>
										
                                        <form method="POST" action="/dashboard" id="edit-form-<?php echo $row['id']; ?>">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
        
                                            <!-- Card content -->
                                            <div class="card-content">
												
												<?php
												/**/
												?>
												
												<!-- Short URL -->
												<p class="card-text card-short-url d-flex align-items-center">
													<span id="short-url-text-<?php echo $row['id']; ?>">
														<!-- <?php echo $site_url . '/'; ?> -->
														<a href="<?php echo $site_url . '/' . $row['short_url']; ?>" target="_blank"><?php echo $site_url . '/' . $row['short_url']; ?></a>
													</span>
													<span id="short-url-input-left-<?php echo $row['id']; ?>" style="display: none;"><?php echo $site_url . '/'; ?></span>
													<input class="short-url-input" type="text" name="new_short_url" id="short-url-input-<?php echo $row['id']; ?>" value="<?php echo $row['short_url']; ?>" style="display: none;">
													
													<!-- Copy to clipboard button -->
													<button type="button" id="card-button-copy-<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm ms-2" onclick="copyToClipboard('<?php echo $site_url . '/' . $row['short_url']; ?>')"><img id="copy-button-icon-<?php echo $row['id']; ?>" class="copy-button-icon button-icon" src="/img/buttons/copy.svg"></button>
													<!-- QR Code button -->
													<button type="button" id="card-button-qr-<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm ms-2" onclick="showQrCodeModal('<?php echo $site_url . '/qrcodes/' . $row['id'] . '.png'; ?>')">
														<img id="qr-button-icon-<?php echo $row['id']; ?>" class="qr-button-icon button-icon" src="/img/buttons/qrcode.svg">
													</button>
													<!-- Edit button -->
													<button type="button" id="card-button-edit-<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm ms-2" onclick="toggleEditAll(<?php echo $row['id']; ?>)"><img id="edit-button-icon-<?php echo $row['id']; ?>" class="edit-button-icon button-icon" src="/img/buttons/edit.svg"></button>
													<!-- Save button -->
													<button type="button" id="card-button-save-<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm ms-2" onclick="saveCardEdit(<?php echo $row['id']; ?>)" style="display: none;"><img id="save-button-icon-<?php echo $row['id']; ?>" class="save-button-icon button-icon" src="/img/buttons/check.svg"></button>
													<!-- Cancel button -->
													<button type="button" id="card-button-cancel-<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm ms-2" onclick="cancelEditAll(<?php echo $row['id']; ?>)" style="display: none;"><img id="cancel-button-icon-<?php echo $row['id']; ?>" class="cancel-button-icon button-icon" src="/img/buttons/close.svg"></button>
													<!-- Delete button -->
													<button type="button" id="card-button-delete-<?php echo $row['id']; ?>" class="btn btn-outline-danger btn-sm ms-2" onclick="if(confirm('Вы уверены, что хотите удалить эту ссылку?')) { window.location.href='?delete=<?php echo $row['id']; ?>&short_url=<?php echo $row['short_url']; ?>'; }"><img id="delete-button-icon-<?php echo $row['id']; ?>" class="delete-button-icon button-icon" src="/img/buttons/delete.svg"></button>
												</p>
												<p class="card-subtitle">Shortened URL</p>
                                                
                                                <!-- Title -->
                                                <p class="card-text">
                                                    <span id="title-text-<?php echo $row['id']; ?>"><?php echo $row['title'] ? $row['title'] : 'Untitled'; ?></span>
                                                    <input type="text" name="new_title" class="title-input" id="title-input-<?php echo $row['id']; ?>" value="<?php echo $row['title']; ?>" style="display: none;">
                                                </p>
                                                <p class="card-subtitle">Title</p>
                                                
                                                <!-- Long URL -->
                                                <p class="card-text">
                                                    <span id="long-url-text-<?php echo $row['id']; ?>"><a href="<?php echo $row['long_url']; ?>" target="_blank"><?php echo $row['long_url']; ?></a></span>
                                                    <input type="text" name="new_long_url" class="long-url-input" id="long-url-input-<?php echo $row['id']; ?>" value="<?php echo $row['long_url']; ?>" style="display: none;">
                                                </p>
                                                <p class="card-subtitle">Destination URL</p>
                                                
												<?php
												/**/
												?>
												
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div class="card-footer">
										
										<div class="grid-container">
										
											<!-- Expiration date (first column, first row) -->
											<div class="grid-item expiration position-relative">
											
												<div id="edit-overlay-expiration-<?php echo $row['id']; ?>" class="edit-overlay-expiration" style="display:none;"></div>
											
												<!-- Expiration date -->
												<p class="card-text d-flex align-items-center">
													<?php if (!empty($row['expiration_date'])): ?>
														<span class="text-primary expiration-link expiration-link-<?php echo $row['id']; ?>" onclick="toggleExpDateEdit(<?php echo $row['id']; ?>)">
															<?php echo $row['expiration_date']; ?>
														</span>
														<input type="datetime-local" name="new_expiration_date" id="expiration-input-<?php echo $row['id']; ?>" value="<?php echo $row['expiration_date']; ?>" style="display: none;">
													<?php else: ?>

														<span class="text-muted expiration-link expiration-link-<?php echo $row['id']; ?>" onclick="toggleExpDateEdit(<?php echo $row['id']; ?>)">Set expiration date</span>
														<input type="datetime-local" name="new_expiration_date" id="expiration-input-<?php echo $row['id']; ?>" style="display: none;">
													<?php endif; ?>

													<!-- Icons for expiration date actions -->
													<span id="exp-date-icons-<?php echo $row['id']; ?>" style="display: none;" class="ms-2">
														<button type="button" id="exp-date-button-save-<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm" onclick="saveExpDate(<?php echo $row['id']; ?>)"><img id="save-exp-date-button-icon-<?php echo $row['id']; ?>" class="save-exp-date-button-icon button-icon" src="/img/buttons/check.svg"></button>
														<button type="button" id="exp-date-button-cancel-<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm" onclick="cancelExpDate(<?php echo $row['id']; ?>)"><img id="cancel-exp-date-button-icon-<?php echo $row['id']; ?>" class="cancel-exp-date-button-icon button-icon" src="/img/buttons/close.svg"></button>
														<button type="button" id="exp-date-button-delete-<?php echo $row['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="deleteExpDate(<?php echo $row['id']; ?>)"><img id="delete-exp-date-button-icon-<?php echo $row['id']; ?>" class="delete-exp-date-button-icon button-icon" src="/img/buttons/delete.svg"></button>
													</span>
												</p>
												<p class="card-subtitle">Expiration date</p>
											</div>

											<!-- Password (spanning two columns, first row) -->
											<div class="grid-item password position-relative" style="grid-column: 2 / span 2;">
												
												<div id="edit-overlay-password-<?php echo $row['id']; ?>" class="edit-overlay-password" style="display:none;"></div>
												
												<!-- Password -->
												<p class="card-text d-flex align-items-center">
												<?php if (!empty($row['password'])): ?>
													<!-- Placeholder for existing password (******) -->
													<span id="password-placeholder-<?php echo $row['id']; ?>" class="text-primary password-link" onclick="togglePasswordEdit(<?php echo $row['id']; ?>)">******</span>
												<?php else: ?>
													<!-- Placeholder for setting a new password -->
													<span id="password-placeholder-<?php echo $row['id']; ?>" class="text-muted password-link" onclick="togglePasswordEdit(<?php echo $row['id']; ?>)">Set password</span>
												<?php endif; ?>
												</p>
												
												<div class="align-items-center mb-2" id="password-field-group-<?php echo $row['id']; ?>" style="display: none !important;"> <!-- d-flex class is controlled by Javascript -->
													<!-- Input field for editing the password -->
													<div class="input-group me-2"> <!-- Используйте me-2 для правого отступа -->
														<input type="password" name="new_password" class="form-control" id="password-input-<?php echo $row['id']; ?>" placeholder="Enter new password">
														<!-- Eye icon button to toggle password visibility -->
														<button type="button" class="btn btn-outline-secondary btn-toggle-password-visibility" id="toggle-password-visibility-<?php echo $row['id']; ?>" onclick="togglePasswordVisibility(<?php echo $row['id']; ?>)"><img class="create-password-eye-icon button-icon" src="/img/buttons/eye.svg"></button>
													</div>

													<!-- Icons for password actions -->
													<div id="password-icons-<?php echo $row['id']; ?>" style="display: none !important;">
														<!-- Save button -->
														<button type="button" id="password-button-save-<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm me-1" onclick="savePassword(<?php echo $row['id']; ?>)"><img id="save-password-button-icon-<?php echo $row['id']; ?>" class="save-password-button-icon button-icon" src="/img/buttons/check.svg"></button>
														<!-- Cancel button -->
														<button type="button" id="password-button-cancel-<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm me-1" onclick="cancelPassword(<?php echo $row['id']; ?>)"><img id="cancel-password-button-icon-<?php echo $row['id']; ?>" class="cancel-password-button-icon button-icon" src="/img/buttons/close.svg"></button>
														<!-- Delete button -->
														<button type="button" id="password-button-delete-<?php echo $row['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="deletePassword(<?php echo $row['id']; ?>)"><img id="delete-password-button-icon-<?php echo $row['id']; ?>" class="delete-password-button-icon button-icon" src="/img/buttons/delete.svg"></button>
													</div>
												</div>
												<p class="card-subtitle">Password</p>
											</div>

											<!-- Footer (second row, 3 columns) -->
											<div class="grid-item short-url-created">
												<p class="card-text text-muted card-footer-value"><?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></p>
												<p class="card-subtitle">Created</p>
											</div>
											<div class="grid-item short-url-hits">
												<p class="card-text text-muted card-footer-value"><?php echo $row['short_count']; ?></p>
												<p class="card-subtitle">Short URL hits</p>
											</div>
											<div class="grid-item qr-code-hits">
												<p class="card-text text-muted card-footer-value"><?php echo $row['qr_count']; ?></p>
												<p class="card-subtitle">QR code hits</p>
											</div>
										</div>
										
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
	</div>
	
	<!-- Modal for errors -->
	<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="errorModalLabel">Error</h5>
			<!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button> -->
		  </div>
		  <div class="modal-body">
			<p id="errorMessage"></p> <!-- Этот элемент должен присутствовать -->
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
		  </div>
		</div>
	  </div>
	</div>
	
<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrCodeModalLabel">QR Code</h5>
      </div>
      <div class="modal-body text-center">
        <!-- QR Code image wrapped in a link -->
        <a id="qrCodeLink" href="" target="_blank">
          <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
        </a>
      </div>
      <div class="modal-footer">
        <a id="qrCodeDownloadLink" href="" download="qrcode.png" class="btn btn-primary">Download</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Обработчик для обновления ссылки на скачивание QR-кода при открытии модального окна
  $('#qrCodeModal').on('show.bs.modal', function () {
    const qrCodeImg = document.getElementById("qrCodeImage");
    const qrCodeLink = document.getElementById("qrCodeDownloadLink");
    const qrCodeOpenLink = document.getElementById("qrCodeLink");

    // Устанавливаем атрибут "href" ссылки для скачивания и открытия QR-кода
    qrCodeLink.href = qrCodeImg.src;
    qrCodeOpenLink.href = qrCodeImg.src;
  });
</script>
	
	<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
	<script src="/js/dashboard.js"></script>
	<script src="/js/sidebar.js"></script>

    </body>

    </html>

    <?php
if ($conn) {
    $conn->close();
}
?>