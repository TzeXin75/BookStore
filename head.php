<?php
// Assume user_role comes from session (set during login)
$user_role = $_SESSION['user_role'] ?? 'guest'; // Default to 'guest' if not logged in
?>

<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<header>
    <div id="info"><?= temp('info') ?></div>
    <div class="header-container">
        <div class="header-left">
            <a href="../index.php" class="logo">SIX SEVEN BS</a>
        </div>


        <div class="header-right">
            <?php if ($user_role !== 'admin'): ?>
                <a href="../cart.php" class="nav-icon-link" title="Cart">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </a>
                <a href="../my_orders.php" class="nav-icon-link" title="My Orders">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </a>
            <?php endif; ?>
        </div>


    </div>
</header>