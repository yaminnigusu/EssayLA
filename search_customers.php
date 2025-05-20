<?php
include 'db.php';

$q = $_GET['q'] ?? '';
if (!$q) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, phone FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY name LIMIT 10");
$search = "%$q%";
$stmt->execute([$search, $search]);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($customers);
