<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $cloth_item = $_POST['cloth_item'];
  $measurement = $_POST['measurement'];
  $cloth_code = $_POST['cloth_code'];
  $id_code = $_POST['id_code'];
  $entry_date = $_POST['entry_date'];
  $delivery_date = $_POST['delivery_date'];
  $total_kilo = $_POST['total_kilo'];
  $price = $_POST['price'];

  $stmt = $pdo->prepare("UPDATE clothing_entries SET 
    cloth_item = ?, 
    measurement = ?, 
    cloth_code = ?, 
    id_code = ?, 
    entry_date = ?, 
    delivery_date = ?, 
    total_kilo = ?, 
    price = ? 
    WHERE id = ?");
  $stmt->execute([
    $cloth_item, $measurement, $cloth_code, $id_code, 
    $entry_date, $delivery_date, $total_kilo, $price, $id
  ]);

  header("Location: view_entries.php"); // Change to your listing page
  exit;
} else {
  $id = $_GET['entry_id'] ?? null;

if (!$id) {
    die("Entry ID not provided");
}
  $stmt = $pdo->prepare("SELECT * FROM clothing_entries WHERE id = ?");
  $stmt->execute([$id]);
  $entry = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$entry) {
    die("Entry not found");
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Clothing Entry</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3>Edit Clothing Entry</h3>
  <form method="POST" class="border p-4 bg-white rounded">
    <input type="hidden" name="id" value="<?= htmlspecialchars($entry['id']) ?>" />

    <div class="mb-3">
      <label>Cloth Item</label>
      <input type="text" name="cloth_item" class="form-control" value="<?= htmlspecialchars($entry['cloth_item']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Quantity</label>
      <input type="text" name="measurement" class="form-control" value="<?= htmlspecialchars($entry['measurement']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Cloth Code</label>
      <input type="text" name="cloth_code" class="form-control" value="<?= htmlspecialchars($entry['cloth_code']) ?>">
    </div>

    <div class="mb-3">
      <label>ID Code</label>
      <input type="text" name="id_code" class="form-control" value="<?= htmlspecialchars($entry['id_code']) ?>">
    </div>

    <div class="mb-3">
      <label>Entry Date</label>
      <input type="date" name="entry_date" class="form-control" value="<?= htmlspecialchars($entry['entry_date']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Delivery Date</label>
      <input type="date" name="delivery_date" class="form-control" value="<?= htmlspecialchars($entry['delivery_date']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Total Kilo</label>
      <input type="number" step="0.01" name="total_kilo" class="form-control" value="<?= htmlspecialchars($entry['total_kilo']) ?>">
    </div>

    <div class="mb-3">
      <label>Price</label>
      <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($entry['price']) ?>">
    </div>

    <button type="submit" class="btn btn-primary">Update Entry</button>
    <a href="view_entries.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
