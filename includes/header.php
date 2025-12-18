<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Book Store</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .navbar { background-color: #333; overflow: hidden; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 16px; text-decoration: none; }
        .navbar a:hover { background-color: #ddd; color: black; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .book-card { border: 1px solid #ddd; padding: 15px; text-align: center; border-radius: 5px; }
        .price { color: #d9534f; font-weight: bold; font-size: 1.2em; }
        .btn { background-color: #28a745; color: white; padding: 10px; text-decoration: none; display: inline-block; margin-top: 10px; border-radius: 4px; border: none; cursor: pointer;}
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php">Home (Products)</a>
    <a href="cart.php">Shopping Cart</a>
    <a href="my_orders.php">My Orders</a> 
    
    <!-- Admin Link for testing -->
    <a href="admin_orders.php" style="float: right; background-color: #555;">Admin Panel</a>
</div>

<div class="container">