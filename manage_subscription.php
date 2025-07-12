<?php
// manage_subscription.php

include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['action'])) {
    die('Invalid request.');
}

$subscriptionId = intval($_GET['id']);
$action = $_GET['action'];

// Fetch existing subscription
$stmt = $pdo->prepare("SELECT * FROM customer_subscriptions WHERE id = ?");
$stmt->execute([$subscriptionId]);
$subscription = $stmt->fetch();

if (!$subscription) {
    die('Subscription not found.');
}

// Process actions
switch ($action) {
    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM customer_subscriptions WHERE id = ?");
        $stmt->execute([$subscriptionId]);
        header("Location: customer_details.php?id=" . $subscription['customer_id']);
        exit;

    case 'suspend':
        $stmt = $pdo->prepare("UPDATE customer_subscriptions SET status = 'suspended' WHERE id = ?");
        $stmt->execute([$subscriptionId]);
        header("Location: customer_details.php?id=" . $subscription['customer_id']);
        exit;

    case 'activate':
        $stmt = $pdo->prepare("UPDATE customer_subscriptions SET status = 'active' WHERE id = ?");
        $stmt->execute([$subscriptionId]);
        header("Location: customer_details.php?id=" . $subscription['customer_id']);
        exit;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $package = $_POST['package_name'];
            $price = floatval($_POST['price']);
            $start = $_POST['start_date'];
            $end = $_POST['end_date'];

            $stmt = $pdo->prepare("
                UPDATE customer_subscriptions
                SET package_name = ?, price = ?, start_date = ?, end_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$package, $price, $start, $end, $subscriptionId]);
            header("Location: customer_details.php?id=" . $subscription['customer_id']);
            exit;
        }
        ?>
        <!-- Edit Form -->
        <!DOCTYPE html>
        <html>
        <head>
            <title>Edit Subscription</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        </head>
        <body class="bg-light p-4">
        <div class="container">
            <h4>Edit Subscription</h4>
            <form method="POST">
                <div class="mb-2">
                    <label>Package Name</label>
                    <input type="text" name="package_name" class="form-control" required value="<?= htmlspecialchars($subscription['package_name']) ?>">
                </div>
                <div class="mb-2">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" required value="<?= htmlspecialchars($subscription['price']) ?>">
                </div>
                <div class="mb-2">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" required value="<?= htmlspecialchars($subscription['start_date']) ?>">
                </div>
                <div class="mb-3">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" required value="<?= htmlspecialchars($subscription['end_date']) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="customer_details.php?id=<?= $subscription['customer_id'] ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        </body>
        </html>
        <?php
        exit;

    default:
        die('Unsupported action.');
}
?>
