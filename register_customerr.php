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
 
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
     <h2 class="mb-4">Customer Registration</h2>
   
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="home.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="register_customer.php">Add Customer</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php">Add Entry</a>
        </li>
        <li class="nav-item">
         
        </li>
      </ul>
    </div>
  </div>
</nav>
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
