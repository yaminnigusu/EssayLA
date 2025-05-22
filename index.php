<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Laundromat Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    /* Positioning for autocomplete suggestions */
    #customer_suggestions {
      max-height: 200px;
      overflow-y: auto;
    }
  </style>
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2 class="mb-4">Customer Registration & Clothing Entry</h2>
<!-- Customer Registration Form -->
<form action="register_customer.php" method="POST" class="border p-4 mb-5 bg-white rounded">
  <h4>Customer Info</h4>
  <div class="mb-3">
    <label for="name" class="form-label">Full Name</label>
    <input type="text" name="name" id="name" class="form-control" required />
  </div>
  <div class="mb-3">
    <label for="phone" class="form-label">Phone Number</label>
    <input type="text" name="phone" id="phone" class="form-control" required />
  </div>
  <div class="mb-3">
    <label for="id_code" class="form-label">ID Code (optional)</label>
    <input type="text" name="id_code" id="id_code" class="form-control" />
  </div>
  <button type="submit" class="btn btn-primary">Register Customer</button>
</form>

<!-- Clothing Entry Form -->
<form action="add_clothing.php" method="POST" class="border p-4 bg-white rounded" id="clothing_form">
  <h4>Clothing Entry</h4>

  <!-- Customer search/select -->
  <div class="mb-3 position-relative">
    <label for="customer_search" class="form-label">Select Customer (by Name or Phone)</label>
    <input type="text" id="customer_search" class="form-control" autocomplete="off" placeholder="Start typing name or phone..." required />
    <input type="hidden" name="customer_id" id="customer_id" required />
    <div id="customer_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
  </div>

  <!-- Cloth items container -->
  <div id="cloth-items-container">
    <div class="cloth-item-row d-flex gap-3 align-items-end mb-3">
      <div class="flex-grow-1">
        <label class="form-label">Cloth Item</label>
        <input type="text" name="cloth_item[]" class="form-control" required />
      </div>

      <div style="width: 120px;">
        <label class="form-label">Quantity</label>
        <input type="number" step="0.01" name="amount[]" class="form-control" required />
      </div>

      <div style="width: 150px;">
        <label class="form-label">Entry Method</label>
        <select name="entry_method[]" class="form-control" required>
        
          <option value="wash">Wash</option>
          <option value="dry_clean">Dry Clean</option>
          <option value="iron">Iron</option>
        </select>
      </div>

      <div>
        <button type="button" class="btn btn-danger btn-sm remove-cloth-item" style="height: 38px;">Remove</button>
      </div>
    </div>
  </div>

  <button type="button" id="add-cloth-item" class="btn btn-secondary mb-4">Add More Cloth Items</button>

  <div class="mb-3">
  <label class="form-label">Total Kilograms</label>
  <input type="text" name="kilograms" class="form-control" />
</div>


  <div class="mb-3">
    <label class="form-label">Cloth Code</label>
    <input type="text" name="cloth_code" class="form-control" />
  </div>

  <div class="mb-3">
    <label class="form-label">ID code</label>
    <input type="text" name="id_code" class="form-control" />
  </div>

  <div class="mb-3">
    <label class="form-label">Entry Date</label>
    <input type="date" name="entry_date" class="form-control" required />
  </div>

  <div class="mb-3">
    <label class="form-label">Delivery Date</label>
    <input type="date" name="delivery_date" class="form-control" required />
  </div>
  <!-- Total price input (for the entire entry) -->
<div class="mb-3">
  <label class="form-label">Total Price</label>
  <input type="number" step="0.01" name="total_price" class="form-control" required />
</div>



  <button type="submit" class="btn btn-success">Add Clothing Entry</button>
</form>

<script>
  const container = document.getElementById('cloth-items-container');
  const addBtn = document.getElementById('add-cloth-item');

  // Function to add remove listener to remove buttons
  function addRemoveListener(button) {
    button.addEventListener('click', () => {
      if (container.querySelectorAll('.cloth-item-row').length > 1) {
        button.closest('.cloth-item-row').remove();
      }
    });
  }

  addBtn.addEventListener('click', () => {
    const firstRow = container.querySelector('.cloth-item-row');
    const newRow = firstRow.cloneNode(true);

    // Clear inputs and reset select in the cloned row
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    const select = newRow.querySelector('select');
    if (select) select.selectedIndex = 0;

    container.appendChild(newRow);

    addRemoveListener(newRow.querySelector('.remove-cloth-item'));
  });

  // Initialize remove button on the first row
  addRemoveListener(container.querySelector('.remove-cloth-item'));

  // Customer search autocomplete
  const searchInput = document.getElementById('customer_search');
  const suggestionsBox = document.getElementById('customer_suggestions');
  const customerIdInput = document.getElementById('customer_id');

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.trim();
    customerIdInput.value = ''; // reset selected customer id
    if (query.length < 2) {
      suggestionsBox.innerHTML = '';
      return;
    }

    fetch(`search_customers.php?q=${encodeURIComponent(query)}`)
      .then(response => response.json())
      .then(data => {
        suggestionsBox.innerHTML = '';
        if (data.length === 0) {
          suggestionsBox.innerHTML = '<div class="list-group-item">No customers found</div>';
          return;
        }
        data.forEach(customer => {
          const div = document.createElement('div');
          div.classList.add('list-group-item', 'list-group-item-action');
          div.textContent = `${customer.name} — ${customer.phone}`;
          div.addEventListener('click', () => {
            searchInput.value = `${customer.name} — ${customer.phone}`;
            customerIdInput.value = customer.id;
            suggestionsBox.innerHTML = '';
          });
          suggestionsBox.appendChild(div);
        });
      })
      .catch(() => {
        suggestionsBox.innerHTML = '<div class="list-group-item text-danger">Error fetching customers</div>';
      });
  });

  // Close suggestions on click outside
  document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
      suggestionsBox.innerHTML = '';
    }
  });

  // Validate customer selection before submit
  document.getElementById('clothing_form').addEventListener('submit', function(e) {
    if (!customerIdInput.value) {
      e.preventDefault();
      alert('Please select a customer from the list.');
      searchInput.focus();
    }
  });
</script>


</body>
</html>
