<?php
// db.php - Database connection file

$host = 'localhost';
$db = 'laundromat_system';
$user = 'root'; // change if your MySQL user is different
$pass = '';     // change if your MySQL password is set

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
