<?php
$host = 'localhost';
$dbname = 'bookly';
$username = 'bookly_user';
$password = '12345678';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
