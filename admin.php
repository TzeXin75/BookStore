<?php 
// 1. CONNECT TO DATABASE
// Load the database connection file first so all pages can use it
require_once 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
</head>
<body>
    <div class="admin-container">
        
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="?page=dashboard" class="active">Dashboard</a></li>
                <li><a href="?page=products">Products</a></li>
                <li><a href="?page=users">Users</a></li>
                <li><a href="?page=settings">Settings</a></li>
                <li><a href="?page=logout">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <?php
            // Get the page name from the URL. If empty, default to 'dashboard'
            $page = $_GET['page'] ?? 'dashboard'; 

            // Check the page name and include the correct PHP file
            if ($page === 'products') {
                // Shows the list of products
                include 'product-dir.php';
            } elseif ($page === 'add_product') {
                // Shows the form to add a new product
                include 'add_product.php';
            } elseif ($page === 'edit_product') {
                // Shows the form to update a product
                include 'edit_product.php';
            } elseif ($page === 'users') {
                // Shows the user management list
                include 'users.php';
            } elseif ($page === 'settings') {
                // Simple placeholder text for settings
                echo "<h2>Settings</h2><p>System settings page under construction...</p>";
            } elseif ($page === 'logout') {
                // Simple placeholder for logout logic
                echo "<h2>Logout</h2><p>You have been logged out.</p>";
            } else {
                // If the page is 'dashboard' or anything else, show the dashboard
                include 'dashboard.php';
            }
            ?>
        </main>
    </div>
</body>
</html>