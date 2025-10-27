<?php
$host = 'localhost';
$dbname = 'resort_booking';
$username = 'root';
$password = ''; // Default WAMP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>