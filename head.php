<?php
// Assume user_role comes from session (set during login)
$user_role = $_SESSION['user_role'] ?? 'guest'; // Default to 'guest' if not logged in
?>

<header>
    <div class="header-container">
        <div class="header-left">
            <a href="index.php" class="logo">BookStore</a>
        </div>

        <div class="header-right">
            <?php if ($user_role !== 'admin'): ?>
                <a href="index.php" class="home-btn">
                    <i class="fas fa-home"></i>
                </a>
                <a href="cart.php" class="cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                </a>
                <a href="my_orders.php" class="order-btn">
                    <i class="fas fa-history"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>