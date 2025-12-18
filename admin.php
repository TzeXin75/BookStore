<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="?page=dashboard" class="active">Dashboard</a></li>
                <li><a href="?page=products">Products</a></li>
                <li><a href="?page=users">Users</a></li>
                <li><a href="?page=manage orders">Manage Orders</a></li>
                <li><a href="?page=settings">Settings</a></li>
                <li><a href="?page=logout">Logout</a></li>
            </ul>
        </aside>

        <!-- Main content area -->
        <main class="main-content">
            <?php
            $page = $_GET['page'] ?? 'dashboard'; // Default page

            if ($page === 'products') {
                include 'products.php';
            } elseif ($page === 'add_product') {
                include 'add_product.php';
            } elseif ($page === 'edit_product') {
                include 'edit_product.php';
            } elseif ($page === 'users') {
                include 'member.php';
            } elseif ($page === 'manage orders') {
                include 'admin_orders.php';
            }elseif ($page === 'settings') {
                echo "<h2>Settings</h2><p>System settings page under construction...</p>";
            } elseif ($page === 'logout') {
                echo "<h2>Logout</h2><p>You have been logged out.</p>";
                header('Location: index.php');
            } else {
                include 'dashboard.php';
            }
            ?>
        </main>
    </div>
</body>
</html>
