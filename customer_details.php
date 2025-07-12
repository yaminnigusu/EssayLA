<?php
include 'db.php';

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch customer info and entries
$stmt = $pdo->prepare("
  SELECT 
    c.name, c.phone, c.id_code AS customer_id_code,
    ce.id AS entry_id, ce.cloth_item, ce.cloth_code, ce.id_code,
    ce.measurement, ce.entry_method, ce.entry_date, ce.delivery_date,
    ce.total_kilo, ce.price
  FROM customers c
  LEFT JOIN clothing_entries ce 
    ON c.id = ce.customer_id AND ce.delivered_status = 0
  WHERE c.id = ?
  ORDER BY ce.entry_date DESC, ce.delivery_date DESC
");
$stmt->execute([$customerId]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$customerInfo = null;
$entries = [];

foreach ($records as $row) {
  if (!$customerInfo) {
    $customerInfo = [
      'name' => $row['name'],
      'phone' => $row['phone'],
      'id_code' => $row['customer_id_code']
    ];
  }
  if ($row['cloth_item']) {
    $entries[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Customer Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="home.php">Laundromat Records</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="register_customerr.php">Add Customer</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php">Add Entry</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
  <h5 class="mt-4">Subscribe to Package</h5>
<!-- Toggle Button -->
<button class="btn btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#subscriptionForm" aria-expanded="false" aria-controls="subscriptionForm">
  ➕ Add Subscription
</button>

<!-- Collapsible Subscription Form -->
<div class="collapse" id="subscriptionForm">
  <div class="card card-body shadow-sm border border-primary">
    <form action="add_subscription.php" method="POST">
      <input type="hidden" name="customer_id" value="<?= $customerId ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Package Name</label>
        <input type="text" name="package_name" class="form-control" placeholder="e.g. Premium Wash" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Price</label>
        <input type="number" step="0.01" name="price" class="form-control" placeholder="e.g. 250" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Start Date</label>
        <input type="date" name="start_date" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">End Date</label>
        <input type="date" name="end_date" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-success w-100">✅ Save Subscription</button>
    </form>
  </div>
</div>

<?php
$sub = $pdo->prepare("SELECT * FROM customer_subscriptions WHERE customer_id = ? ORDER BY end_date DESC LIMIT 1");
$sub->execute([$customerId]);
$latest = $sub->fetch();
?>

<?php if ($latest): ?>
  <div class="row mt-3">
    <div class="col-md-6 col-lg-5">
      <div class="card border-info shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-start">
          <div>
            <h6 class="text-info fw-bold mb-2">Latest Subscription</h6>
            <p class="mb-1"><strong>Package:</strong> <?= htmlspecialchars($latest['package_name']) ?></p>
            <p class="mb-1"><strong>Price:</strong> <?= number_format($latest['price'], 2) ?></p>
            <p class="mb-1"><strong>From:</strong> <?= $latest['start_date'] ?> <strong>to</strong> <?= $latest['end_date'] ?></p>
            
          </div>

          <div class="ms-3 d-flex flex-column gap-1">
            <a href="manage_subscription.php?id=<?= $latest['id'] ?>&action=edit" 
               class="btn btn-sm btn-outline-primary px-2 py-1" 
               data-bs-toggle="tooltip" title="Edit">
              <i class="bi bi-pencil-square"></i>
            </a>

            <a href="manage_subscription.php?id=<?= $latest['id'] ?>&action=delete" 
               class="btn btn-sm btn-outline-danger px-2 py-1" 
               onclick="return confirm('Delete this subscription?');"
               data-bs-toggle="tooltip" title="Delete">
              <i class="bi bi-trash"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<br>



  <a href="home.php" class="btn btn-secondary mb-3">← Back to Home</a>

  <?php if ($customerInfo): ?>
    <h3><?= htmlspecialchars($customerInfo['name']) ?> <small class="text-muted">(<?= htmlspecialchars($customerInfo['phone']) ?>)</small></h3>
    <p>Customer ID Code: <?= htmlspecialchars($customerInfo['id_code'] ?? 'N/A') ?></p>

    <?php if (empty($entries)): ?>
      <div class="alert alert-info">No undelivered clothing entries found for this customer.</div>
    <?php else: ?>
      <h5 class="mt-4">Undelivered Clothing Entries:</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-hover mt-2">
          <thead>
            <tr>
              <th>Entry Date</th>
              <th>Delivery Date</th>
              <th>Item</th>
              <th>Color Code</th>
              <th>ID Code</th>
              <th>Qty</th>
              <th>Kilos</th>
              <th>Price</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($entries as $item): ?>
              <tr>
                <td><?= htmlspecialchars($item['entry_date']) ?></td>
                <td><?= htmlspecialchars($item['delivery_date']) ?></td>
                <td><?= htmlspecialchars($item['cloth_item']) ?></td>
                <td><?= htmlspecialchars($item['cloth_code']) ?></td>
                <td><?= htmlspecialchars($item['id_code']) ?></td>
                <td><?= htmlspecialchars($item['measurement']) ?></td>
                <td><?= htmlspecialchars($item['total_kilo'] ?? 'N/A') ?></td>
                <td><?= isset($item['price']) ? number_format($item['price'], 2) : 'N/A' ?></td>
                <td>
                  <a href="edit_entry.php?id=<?= $item['entry_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                  <a href="delete_entry.php?id=<?= $item['entry_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this entry?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="alert alert-danger">Customer not found.</div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
</script>
</body>
</html>
