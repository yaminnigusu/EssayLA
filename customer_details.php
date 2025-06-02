<!-- customer_details.php -->
<div class="container mt-4">
  <?php
  // Get customer ID from URL
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">Invalid customer ID.</div>';
    exit;
  }

  $customerId = intval($_GET['id']);

  // Fetch customer info
  $stmt = $pdo->prepare("
    SELECT id, name, phone, id_code 
    FROM customers 
    WHERE id = ?
  ");
  $stmt->execute([$customerId]);
  $customer = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$customer) {
    echo '<div class="alert alert-danger">Customer not found.</div>';
    exit;
  }

  // Fetch clothing entries
  $stmt = $pdo->prepare("
    SELECT 
      id, cloth_item, cloth_code, id_code, measurement, 
      entry_method, entry_date, delivery_date, total_kilo, price 
    FROM clothing_entries 
    WHERE customer_id = ? 
    ORDER BY entry_date DESC, delivery_date DESC, id DESC
  ");
  $stmt->execute([$customerId]);
  $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <h2 class="mb-4">Details for <?= htmlspecialchars($customer['name']) ?></h2>
  <p><strong>Phone:</strong> <?= htmlspecialchars($customer['phone']) ?></p>
  <p><strong>ID Code:</strong> <?= htmlspecialchars($customer['id_code']) ?: 'N/A' ?></p>
  <a href="customers.php" class="btn btn-secondary mb-4">Back to All Customers</a>

  <?php if (empty($entries)): ?>
    <div class="alert alert-info">No clothing entries found for this customer.</div>
  <?php else: ?>
    <?php
    // Group by entry_date + delivery_date
    $groupedEntries = [];
    foreach ($entries as $entry) {
      $dateKey = $entry['entry_date'] . '|' . $entry['delivery_date'];
      if (!isset($groupedEntries[$dateKey])) {
        $groupedEntries[$dateKey] = [
          'entry_date' => $entry['entry_date'],
          'delivery_date' => $entry['delivery_date'],
          'items' => []
        ];
      }
      $groupedEntries[$dateKey]['items'][] = $entry;
    }
    ?>

    <?php 
    $entryNumber = 1;
    foreach ($groupedEntries as $group):
      $firstItem = $group['items'][0];
    ?>
      <div class="card mb-4">
        <div class="card-body">
          <h5>
            Entry #<?= $entryNumber++ ?>:
            Entry Date: <?= htmlspecialchars($group['entry_date']) ?> |
            Delivery Date: <?= htmlspecialchars($group['delivery_date']) ?>
          </h5>
          <p>
            <strong>Color Code:</strong> <?= htmlspecialchars($firstItem['cloth_code'] ?: 'N/A') ?> |
            <strong>ID Code:</strong> <?= htmlspecialchars($firstItem['id_code'] ?: 'N/A') ?> |
            <strong>Total Kilos:</strong> <?= $firstItem['total_kilo'] !== null ? htmlspecialchars($firstItem['total_kilo']) : 'N/A' ?> |
            <strong>Price:</strong> <?= isset($firstItem['price']) ? number_format($firstItem['price'], 2) : 'N/A' ?>
          </p>

          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Cloth Item</th>
                <th>Quantity</th>
                <th>Entry Method</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($group['items'] as $item): ?>
                <tr>
                  <td><?= htmlspecialchars($item['cloth_item']) ?></td>
                  <td><?= htmlspecialchars($item['measurement']) ?></td>
                  <td><?= htmlspecialchars($item['entry_method']) ?></td>
                  <td>
                    <a href="edit_entry.php?id=<?= $item['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete_entry.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
