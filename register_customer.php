<?php
include 'db.php';

$name = $_POST['name'];
$phone = $_POST['phone'];
$id_code = $_POST['id_code'];

$stmt = $pdo->prepare("INSERT INTO customers (name, phone, id_code) VALUES (?, ?, ?)");
$stmt->execute([$name, $phone, $id_code]);

header("Location: index.php");
exit;
?>
