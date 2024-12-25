<?php
function isShortUrlUnique($short_url, $id) {
    global $conn; // Use the global connection

    $stmt = $conn->prepare("SELECT COUNT(*) FROM four WHERE short_url = ? AND id != ?");
    $stmt->bind_param("si", $short_url, $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count === 0; // Return true if the short URL is unique
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['short_url'])) {
    $short_url = filter_var($_POST['short_url'], FILTER_SANITIZE_STRING);
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    // Call the function to check if the short URL is unique
    $is_unique = isShortUrlUnique($short_url, $id);

    // Return the result as JSON
    echo json_encode(['is_unique' => $is_unique]);
    exit();
}
?>