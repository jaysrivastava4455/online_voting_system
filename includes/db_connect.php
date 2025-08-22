<?php
session_start();

$host = "localhost:3307";
$db   = "online_voting_system";
$user = "root";
$pass = "";

try {
    // Establish the connection
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

    // Set the error handling attribute (this one is essential for security and debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

define("BASE_URL", "http://localhost/online-voting-system/");
?>