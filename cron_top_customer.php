<?php
require 'db.php';

// Get last month
$monthYear = date('Y-m', strtotime('first day of last month'));

// Check if stats already recorded
$check = $pdo->prepare("SELECT COUNT(*) FROM monthly_stats WHERE month_year = ?");
$check->execute([$monthYear]);
if ($check->fetchColumn() > 0) {
    exit("Already recorded for $monthYear.");
}

// Fetch top customer by total kilos for last month
$top = $pdo->prepare("
    SELECT ce.customer_id, COUNT(*) AS entry_count, SUM(ce.total_kilo) AS total_kilos
    FROM clothing_entries ce
    WHERE DATE_FORMAT(ce.entry_date, '%Y-%m') = ?
    GROUP BY ce.customer_id
    ORDER BY total_kilos DESC
    LIMIT 1
");
$top->execute([$monthYear]);
$topCustomer = $top->fetch(PDO::FETCH_ASSOC);

if ($topCustomer) {
    $insert = $pdo->prepare("
        INSERT INTO monthly_stats (month_year, customer_id, entry_count, total_kilos)
        VALUES (?, ?, ?, ?)
    ");
    $insert->execute([
        $monthYear,
        $topCustomer['customer_id'],
        $topCustomer['entry_count'],
        $topCustomer['total_kilos']
    ]);
    echo "Top customer for $monthYear saved.";
} else {
    echo "No entries found for $monthYear.";
}
