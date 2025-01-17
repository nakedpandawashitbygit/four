<?php
//$site_url = "http://h406470147.nichost.ru";
$site_url = "http://lnk.monster";
$servername = "h406470147.mysql";
$username = "h406470147_mysql";
$password = "_ap8LTKB";
$dbname = "h406470147_db";

$logging_enabled = true;

$default_long_url_image_url = "/img/no-meta-img.png";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>