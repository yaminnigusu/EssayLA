<?php
require 'db.php';

$customer_id = $_POST['customer_id'];
$package_name = $_POST['package_name'];
$price = $_POST['price'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

$stmt = $pdo->prepare("INSERT INTO customer_subscriptions (customer_id, package_name, price, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$customer_id, $package_name, $price, $start_date, $end_date]);

header("Location: customer_details.php?id=$customer_id&subscription=success");
exit;
