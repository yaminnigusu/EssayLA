<?php
// home.php
include 'db.php';
// Fetch Total Customers
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

// Fetch Total Entries
$totalEntries = $pdo->query("SELECT COUNT(*) FROM clothing_entries")->fetchColumn();

// Fetch Most Frequent Customer
// Fetch Most Frequent Customer by Total Kilos
$topCustomerStmt = $pdo->query("
  SELECT 
    c.name, 
    c.phone, 
    COUNT(*) AS entry_count,
    SUM(ce.total_kilo) AS total_kilos
  FROM clothing_entries ce
  JOIN customers c ON ce.customer_id = c.id
  WHERE ce.total_kilo IS NOT NULL
  GROUP BY ce.customer_id
  ORDER BY total_kilos DESC
  LIMIT 1
");
$topCustomer = $topCustomerStmt->fetch(PDO::FETCH_ASSOC);

// Fetch Most Common Cloth Item
$topItemStmt = $pdo->query("
  SELECT cloth_item, COUNT(*) AS item_count
  FROM clothing_entries
  GROUP BY cloth_item
  ORDER BY item_count DESC
  LIMIT 1
");
$topItem = $topItemStmt->fetch(PDO::FETCH_ASSOC);
// Get current month’s stat
$currentMonth = date('Y-m');
$stmt = $pdo->prepare("
    SELECT c.name, c.phone, ms.entry_count, ms.total_kilos
    FROM monthly_stats ms
    JOIN customers c ON c.id = ms.customer_id
    WHERE ms.month_year = ?
");
$stmt->execute([$currentMonth]);
$topMonthly = $stmt->fetch(PDO::FETCH_ASSOC);
// Fetch all subscription statuses
$subStmt = $pdo->query("SELECT customer_id FROM customer_subscriptions WHERE CURDATE() BETWEEN start_date AND end_date");
$subscribedCustomers = $subStmt->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Laundromat Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .search-box {
      max-width: 400px;
    }
  </style>
</head>
<body class="bg-light">

<!-- Navigation Bar -->
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
  <h2 class="mb-4">All Customers & Undelivered Clothing Entries</h2>
  <div class="row mb-4">
    <?php if ($topMonthly): ?>
  <div class="alert alert-info">
    <strong>Top Customer This Month:</strong> <?= htmlspecialchars($topMonthly['name']) ?> (<?= htmlspecialchars($topMonthly['phone']) ?>)<br>
    <?= $topMonthly['entry_count'] ?> entries | <?= number_format($topMonthly['total_kilos'], 2) ?> kg
  </div>
<?php endif; ?>
  <div class="col-md-3">
    <div class="card text-white bg-primary mb-3">
      <div class="card-body">
        <h5 class="card-title">Total Customers</h5>
        <p class="card-text fs-4"><?= $totalCustomers ?></p>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card text-white bg-success mb-3">
      <div class="card-body">
        <h5 class="card-title">Total Clothing Entries</h5>
        <p class="card-text fs-4"><?= $totalEntries ?></p>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card text-white bg-warning mb-3">
      <div class="card-body">
        <h5 class="card-title">Top Customer</h5>
        <?php if ($topCustomer): ?>
  <p class="card-text mb-0"><strong><?= htmlspecialchars($topCustomer['name']) ?></strong></p>
  <small class="text-light">
    <?= htmlspecialchars($topCustomer['phone']) ?><br>
    <?= $topCustomer['entry_count'] ?> entries | <?= number_format($topCustomer['total_kilos'], 2) ?> kg
  </small>
<?php else: ?>
  <p class="card-text">N/A</p>
<?php endif; ?>

      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card text-white bg-secondary mb-3">
      <div class="card-body">
        <h5 class="card-title">Top Cloth Item</h5>
        <?php if ($topItem): ?>
          <p class="card-text"><?= htmlspecialchars($topItem['cloth_item']) ?> (<?= $topItem['item_count'] ?> times)</p>
        <?php else: ?>
          <p class="card-text">N/A</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

  <!-- 🔍 Search Filter -->
 <!-- 🔍 Search Filter -->
<div class="row mb-4">
  <div class="col-md-8">
    <input type="text" id="searchInput" class="form-control" placeholder="Search by name or phone...">
  </div>
  <div class="col-md-4">
    <select id="subscriptionFilter" class="form-select">
      <option value="">All</option>
      <option value="subscribed">Subscribed</option>
      <option value="not_subscribed">Not Subscribed</option>
    </select>
  </div>
</div>


  <?php
  $subStmt = $pdo->query("
  SELECT customer_id 
  FROM customer_subscriptions 
  WHERE CURDATE() BETWEEN start_date AND end_date
");
$subscribedCustomers = $subStmt->fetchAll(PDO::FETCH_COLUMN);

  $stmt = $pdo->query("
    SELECT 
      c.id AS customer_id,
      c.name,
      c.phone,
      c.id_code AS customer_id_code,
      ce.id AS entry_id,
      ce.cloth_item,
      ce.cloth_code,
      ce.id_code,
      ce.measurement,
      ce.entry_method,
      ce.entry_date,
      ce.delivery_date,
      ce.total_kilo,
      ce.price,
      ce.delivered_status
    FROM customers c
    LEFT JOIN clothing_entries ce 
      ON c.id = ce.customer_id AND ce.delivered_status = 0
    ORDER BY c.id DESC, ce.entry_date DESC, ce.delivery_date DESC, ce.id DESC
  ");

  $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $groupedEntries = [];

  if ($records):
    foreach ($records as $row) {
      $cid = $row['customer_id'];
      if (!isset($groupedEntries[$cid])) {
        $groupedEntries[$cid] = [
          'info' => [
            'name' => $row['name'],
            'phone' => $row['phone'],
            'id_code' => $row['customer_id_code']
          ],
          'entries' => []
        ];
      }
      if ($row['cloth_item']) {
        $dateKey = $row['entry_date'] . '|' . $row['delivery_date'];
        if (!isset($groupedEntries[$cid]['entries'][$dateKey])) {
          $groupedEntries[$cid]['entries'][$dateKey] = [
            'entry_date' => $row['entry_date'],
            'delivery_date' => $row['delivery_date'],
            'items' => []
          ];
        }
        $groupedEntries[$cid]['entries'][$dateKey]['items'][] = [
          'entry_id' => $row['entry_id'],
          'cloth_item' => $row['cloth_item'],
          'cloth_code' => $row['cloth_code'],
          'id_code' => $row['id_code'],
          'measurement' => $row['measurement'],
          'entry_method' => $row['entry_method'],
          'total_kilo' => $row['total_kilo'],
          'price' => $row['price']
        ];
      }
    }
  ?>
<div class="row" id="cardContainer">
 <?php foreach ($groupedEntries as $customerId => $customerData): 
  $isSubscribed = in_array($customerId, $subscribedCustomers);
?>
  <div class="col-md-6 col-lg-4 mb-4 customer-card" 
       data-name="<?= strtolower($customerData['info']['name']) ?>"
       data-phone="<?= $customerData['info']['phone'] ?>"
       data-subscription="<?= $isSubscribed ? 'subscribed' : 'not_subscribed' ?>">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <h5 class="card-title customer-name mb-1">
          <?= htmlspecialchars($customerData['info']['name']) ?>
          <small class="text-muted">(<?= htmlspecialchars($customerData['info']['phone']) ?>)</small>
        </h5>
        <p class="mb-1">Customer ID Code: <?= htmlspecialchars($customerData['info']['id_code']) ?: 'N/A' ?></p>
        <p class="fw-bold mb-2 text-success">Total Undelivered Entries: <?= count($customerData['entries']) ?></p>

        <?php if ($isSubscribed): ?>
          <span class="badge bg-success mb-2">Subscribed</span>
        <?php else: ?>
          <span class="badge bg-secondary mb-2">Not Subscribed</span>
        <?php endif; ?>

        <a href="customer_details.php?id=<?= $customerId ?>" class="btn btn-outline-primary btn-sm">View Details</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const filterSelect = document.getElementById("subscriptionFilter");
  const cards = document.querySelectorAll(".customer-card");

  function filterCards() {
    const searchTerm = searchInput.value.toLowerCase();
    const filterValue = filterSelect.value;

    cards.forEach(card => {
      const name = card.dataset.name;
      const phone = card.dataset.phone;
      const subscription = card.dataset.subscription;

      const matchesSearch = name.includes(searchTerm) || phone.includes(searchTerm);
      const matchesFilter = !filterValue || subscription === filterValue;

      if (matchesSearch && matchesFilter) {
        card.style.display = "";
      } else {
        card.style.display = "none";
      }
    });
  }

  searchInput.addEventListener("input", filterCards);
  filterSelect.addEventListener("change", filterCards);
});
</script>

<!-- 🔎 Live Search Script -->
<script>
  const searchInput = document.getElementById('searchInput');
  const cards = document.querySelectorAll('.customer-card');

  searchInput.addEventListener('keyup', function () {
    const term = this.value.toLowerCase();

    cards.forEach(card => {
      const name = card.querySelector('.customer-name').textContent.toLowerCase();
      const phone = card.querySelector('.customer-phone').textContent.toLowerCase();

      if (name.includes(term) || phone.includes(term)) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  });
</script>
<script>
  document.querySelectorAll('.toggle-details').forEach(card => {
    card.addEventListener('click', () => {
      const targetId = card.getAttribute('data-target');
      const details = document.getElementById(targetId);
      if (details) {
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
      }
    });
  });
</script>

</body>
</html>
<?php endif; ?>
