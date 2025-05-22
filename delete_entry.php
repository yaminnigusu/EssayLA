<?php
include 'db.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $pdo->prepare("DELETE FROM clothing_entries WHERE id = ?");
  $stmt->execute([$id]);
}

header("Location: home.php.php"); // Change this to your listing page
exit;
