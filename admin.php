<?php
// admin.php
session_start();
require_once 'config/db_connect.php';
include '_base.php';

// GLOBAL SECURITY CHECK: If not admin, kick them out immediately
if (!isset($_SESSION['user']) || $_SESSION['user']['user_role'] !== 'admin') {
    header('Location: login.php'); // Redirect to login page
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="icon" type="image/webp" href="uploads/favicon.webp?v=1">
    <link rel="shortcut icon" href="uploads/favicon.webp?v=1" type="image/webp">
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="?page=dashboard">Dashboard</a></li>
                <li><a href="?page=product_dir">Products</a></li>
                <li><a href="?page=batch_insert">Batch Add Products</a></li>
                <li><a href="?page=users">Users</a></li>
                <li><a href="?page=manage orders">Manage Orders</a></li>
                <li><a href="index.php">Index</a></li>
                <li><a href="?page=logout">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <?php
            $page = $_GET['page'] ?? 'dashboard';

            if ($page === 'product_dir') {
                include 'product_dir.php';
            } elseif ($page === 'add_product') {
                include 'add_product.php';
            } elseif ($page === 'batch_insert') {
            include 'batch_insert.php';
            } elseif ($page === 'process_batch_insert') {
            include 'process_batch_insert.php';
            } elseif ($page === 'edit_product') {
                include 'edit_product.php';
            } elseif ($page === 'users') {
                include './userlist/user.php';
            } elseif ($page === 'user_details') {
                include './userlist/user_details.php';
            } elseif ($page === 'manage orders') {
                include 'admin_orders.php';
            } elseif ($page === 'order_details') {
                include 'admin_order_details.php';
            } elseif ($page === 'cancelled_orders') {
                include 'cancelled_orders.php';
            } elseif ($page === 'cancelled_orders') {
                include 'cancelled_orders.php';
            } elseif ($page === 'index') {
                include 'index.php';
            } elseif ($page === 'logout') {
                $_SESSION = array();
                session_destroy();
                header('Location: index.php');
                exit();
            } else {
                include 'dashboard.php';
            }
            ?>
        </main>
    </div>
    <div id="info"><?= temp('info') ?></div>
</body>
</html>