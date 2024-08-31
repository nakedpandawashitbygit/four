<?php
$servername = "h406470147.mysql";
$username = "h406470147_mysql";
$password = "_ap8LTKB";
$dbname = "h406470147_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>