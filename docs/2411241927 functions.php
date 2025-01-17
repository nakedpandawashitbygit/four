<?php
require_once 'config.php';

function shortenUrl($length = 6) {
    return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", $length)), 0, $length);
}

// Function to log events or errors
function logMessage($message, $type = 'INFO') {
    global $logging_enabled;
    if (!$logging_enabled) {
        return;
    }

    $logFile = __DIR__ . '/logs/app_log.txt'; // Define the log file location

    if (!file_exists(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0777, true);
    }

    // Collect user data
    $userId = $_SESSION['user_id'] ?? 'guest'; // Track user ID if logged in, or mark as guest
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown'; // Get user's IP address
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'; // Get user's browser info

    // Add timestamp, log type, and user data to the message
    $logMessage = date('Y-m-d H:i:s') . " [$type] - User: $userId, IP: $ipAddress, User-Agent: $userAgent - $message" . PHP_EOL;

    // Write the message to the log file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to fetch the title from a given URL
function fetchTitleFromURL($url) {
    // Suppress errors from invalid URLs or inaccessible pages
    $html = @file_get_contents($url);

    if ($html !== false) {
        // Parse the HTML content
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $title = $doc->getElementsByTagName('title');
        
        // Check if a title tag exists
        if ($title->length > 0) {
            return trim($title->item(0)->nodeValue);
        }
    }

    // Return "Untitled" if title could not be fetched
    return 'Untitled';
}

// Function to fetch an image from the meta tags of a given URL
function fetchImageFromURL($url) {
    global $default_long_url_image_url; // Use the variable from config.php

    // Suppress errors from invalid URLs or inaccessible pages
    $html = @file_get_contents($url);

    if ($html !== false) {
        // Parse the HTML content
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $meta_tags = $doc->getElementsByTagName('meta');

        // Iterate through meta tags to find the image
        foreach ($meta_tags as $meta) {
            if ($meta->getAttribute('property') === 'og:image' || $meta->getAttribute('name') === 'twitter:image') {
                $image_url = $meta->getAttribute('content');
                if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                    return $image_url;
                }
            }
        }
    }

    // Return the default image if no valid image URL is found
    return $default_long_url_image_url;
}

function shortUrlExists($conn, $short_url, $id = null) {
    // Prepare the SQL statement
    $sql = "SELECT COUNT(*) FROM four WHERE short_url = ?" . ($id ? " AND id != ?" : "");
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    if ($id) {
        $stmt->bind_param("si", $short_url, $id);
    } else {
        $stmt->bind_param("s", $short_url);
    }
    
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0; // Returns true if the short URL exists, false otherwise
}

function longUrlExists($conn, $long_url, $id = null) {
    $sql = "SELECT COUNT(*) FROM four WHERE long_url = ?" . ($id ? " AND id != ?" : "");
    $stmt = $conn->prepare($sql);
    
    if ($id) {
        $stmt->bind_param("si", $long_url, $id);
    } else {
        $stmt->bind_param("s", $long_url);
    }
    
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    return $count > 0; // Returns true if the long URL exists, false otherwise
}
