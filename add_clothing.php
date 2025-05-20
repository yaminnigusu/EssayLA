<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $cloth_items = $_POST['cloth_item'] ?? [];
    $entry_methods = $_POST['entry_method'] ?? [];
    $amounts = $_POST['amount'] ?? [];
    $prices = $_POST['price'] ?? [];
    $cloth_code = $_POST['cloth_code'] ?? '';
    $id_code = $_POST['id_code'] ?? '';
    $entry_date = $_POST['entry_date'] ?? '';
    $delivery_date = $_POST['delivery_date'] ?? '';

    // Get total kilograms from the form (optional)
    $total_kilo = isset($_POST['kilo']) && is_numeric($_POST['kilo']) ? floatval($_POST['kilo']) : null;

    // Validate required fields
    if (!$customer_id) {
        die('Error: Customer not selected.');
    }
    if (!$entry_date || !$delivery_date) {
        die('Error: Entry and delivery dates are required.');
    }
    if (
        count($cloth_items) === 0
        || count($cloth_items) !== count($entry_methods)
        || count($cloth_items) !== count($amounts)
        || count($cloth_items) !== count($prices)
    ) {
        die('Error: Clothing data is incomplete or mismatched.');
    }

    try {
        // Verify customer exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        if (!$stmt->fetch()) {
            die('Error: Selected customer does not exist.');
        }

        // Prepare insert statement including total_kilo
        $stmt = $pdo->prepare("INSERT INTO clothing_entries 
            (customer_id, cloth_item, cloth_code, id_code, entry_method, measurement, entry_date, delivery_date, price, total_kilo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        for ($i = 0; $i < count($cloth_items); $i++) {
            $item = trim($cloth_items[$i]);
            $method = trim($entry_methods[$i]);
            $amount = floatval($amounts[$i]);
            $price = floatval($prices[$i]);
            $measurement = $amount; // Adjust if measurement is different from amount

            if ($item === '') {
                continue; // skip empty items
            }

            $stmt->execute([
                $customer_id,
                $item,
                $cloth_code,
                $id_code,
                $method,
                $measurement,
                $entry_date,
                $delivery_date,
                $price,
                $total_kilo // total_kilo inserted same for all items
            ]);
        }

        header("Location: index.php?msg=Entries added successfully");
        exit;

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die('Invalid request method.');
}
