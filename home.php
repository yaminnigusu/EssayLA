<?php
// home.php
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Laundromat Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2 class="mb-4">All Customers & Clothing Entries</h2>

  <?php
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
      ce.price
    FROM customers c
    LEFT JOIN clothing_entries ce ON c.id = ce.customer_id
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

  <?php foreach ($groupedEntries as $customerId => $customerData): ?>
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">
          <?= htmlspecialchars($customerData['info']['name']) ?>
          <small class="text-muted">(<?= htmlspecialchars($customerData['info']['phone']) ?>)</small>
        </h5>
        <p class="card-text">Customer ID Code: <?= htmlspecialchars($customerData['info']['id_code']) ?: 'N/A' ?></p>

        <?php if (empty($customerData['entries'])): ?>
          <p class="fst-italic text-muted">No clothing entries found for this customer.</p>
        <?php else: ?>
          <p><strong>Total Entries: <?= count($customerData['entries']) ?></strong></p>

          <?php 
          $entryNumber = 1;
          foreach ($customerData['entries'] as $dateKey => $entryGroup): 
            $firstItem = $entryGroup['items'][0];
          ?>
            <h6 class="mt-3">
              Entry #<?= $entryNumber++ ?>: 
              Entry Date: <?= htmlspecialchars($entryGroup['entry_date']) ?> | 
              Delivery Date: <?= htmlspecialchars($entryGroup['delivery_date']) ?>
            </h6>

            <p>
              <strong>Color Code:</strong> <?= htmlspecialchars($firstItem['cloth_code'] ?: 'N/A') ?> &nbsp;&nbsp;|&nbsp;&nbsp;
              <strong>ID Code:</strong> <?= htmlspecialchars($firstItem['id_code'] ?: 'N/A') ?> &nbsp;&nbsp;|&nbsp;&nbsp;
              <strong>Total Kilos:</strong> <?= $firstItem['total_kilo'] !== null ? htmlspecialchars($firstItem['total_kilo']) : 'N/A' ?> &nbsp;&nbsp;|&nbsp;&nbsp;
              <strong>Price:</strong> <?= isset($firstItem['price']) ? number_format($firstItem['price'], 2) : 'N/A' ?>
            </p>

            <table class="table table-bordered mt-2">
              <thead>
                <tr>
                  <th>Cloth Item</th>
                  <th>Quantity</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($entryGroup['items'] as $item): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['cloth_item']) ?></td>
                    <td><?= htmlspecialchars($item['measurement']) ?></td>
                    <td>
                      <a href="edit_entry.php?id=<?= $item['entry_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                      <a href="delete_entry.php?id=<?= $item['entry_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php else: ?>
    <div class="alert alert-info">No records found.</div>
  <?php endif; ?>
</div>

</body>
</html>
