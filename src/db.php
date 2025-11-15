<?php
// Database connection using Render MySQL environment variables
$host = getenv('RENDER_DB_HOST');  // Render MySQL host
$db   = getenv('RENDER_DB_NAME');  // Database name
$user = getenv('RENDER_DB_USER');  // Database username
$pass = getenv('RENDER_DB_PASSWORD'); // Database password
$port = getenv('RENDER_DB_PORT');   // Usually 5432 for PostgreSQL, 3306 for MySQL

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
