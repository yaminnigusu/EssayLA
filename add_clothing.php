<?php
require 'db.php'; // Adjust path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? null;
    $cloth_items = $_POST['cloth_item'] ?? [];
    $amounts = $_POST['amount'] ?? [];
    $entry_methods = $_POST['entry_method'] ?? [];
    $cloth_code = $_POST['cloth_code'] ?? null;
    $id_code = $_POST['id_code'] ?? null;
    $entry_date = $_POST['entry_date'] ?? null;
    $delivery_date = $_POST['delivery_date'] ?? null;
    $total_kilo = $_POST['kilograms'] ?? null; // ✅ FIXED: single value
    $total_price = $_POST['total_price'] ?? null;

    // Basic validation
    if (!$customer_id || !$entry_date || !$delivery_date || !$total_price || empty($cloth_items)) {
        die('Missing required fields.');
    }

    // Prepare the insert statement
    $stmt = $pdo->prepare("
        INSERT INTO clothing_entries 
        (customer_id, cloth_item, cloth_code, id_code, entry_method, measurement, entry_date, delivery_date, price, total_kilo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Insert each clothing item row
    for ($i = 0; $i < count($cloth_items); $i++) {
        $item = trim($cloth_items[$i]);
        $method = trim($entry_methods[$i] ?? '');
        $amount = floatval($amounts[$i] ?? 0);

        if ($item === '') continue;

        $stmt->execute([
            $customer_id,
            $item,
            $cloth_code,
            $id_code,
            $method,
            $amount,
            $entry_date,
            $delivery_date,
            floatval($total_price), // Same total price applied
            floatval($total_kilo)
        ]);
    }

    header("Location: success.php");
    exit;
} else {
    die('Invalid request method.');
}
