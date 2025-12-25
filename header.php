<?php require '_base.php'; ?>

<head>
    <link rel="icon" type="image/png" href="uploads/favicon.png">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<header class="site-header">
    <div class="header-container">
        <div class="header-left">
            <a href="index.php" class="logo">BookStore</a>
        </div>

        <div class="header-center">
            <form class="search-form" action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search by title, author..." required>
                <button type="submit" class="search-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
        </div>

        <div class="header-right">
            <a href="cart.php" class="nav-icon-link" title="Cart">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            </a>

            <a href="my_orders.php" class="nav-icon-link" title="My Orders">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            </a>

            <div class="user-dropdown">
                <?php if (isset($_user)): ?>
                    <button class="user-trigger">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span class="username"><?= htmlspecialchars($_user['username']) ?></span>
                        <span class="arrow-small">▼</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="user/profile.php">My Profile</a>
                        <?php if($_user['user_role'] === 'admin'): ?>
                            <a href="admin.php">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="nav-icon-link">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="category-nav">
        <div class="nav-item">
            <a href="#" class="cat-link">Fiction</a>
            <div class="mega-menu">
                <a href="category.php?sub=Novel">Novel</a>
                <a href="category.php?sub=Comic">Comic</a>
            </div>
        </div>
        <div class="nav-item">
            <a href="#" class="cat-link">Non-Fiction</a>
            <div class="mega-menu">
                <a href="category.php?sub=Biography">Biography</a>
                <a href="category.php?sub=Self-help">Self-help</a>
            </div>
        </div>
        <div class="nav-item">
            <a href="#" class="cat-link">Education</a>
            <div class="mega-menu">
                <a href="category.php?sub=Textbook">Textbook</a>
            </div>
        </div>
        <div class="nav-item">
            <a href="#" class="cat-link">Children</a>
            <div class="mega-menu">
                <a href="category.php?sub=Color Book">Color Book</a>
            </div>
        </div>
    </nav>
    <div id="info"><?= temp('info') ?></div>
</header>

<style>
/* 1. Global Header Reset */
.site-header {
    width: 100%;
    background: #2c3e50;
    position: relative;
    z-index: 1000;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* 2. Logo & Search */
.logo { font-size: 1.5rem; font-weight: bold; color: white; text-decoration: none; }
.search-form { display: flex; background: white; border-radius: 4px; overflow: hidden; }
.search-form input { border: none; padding: 8px 15px; width: 300px; outline: none; }
.search-btn { background: #3498db; border: none; color: white; padding: 0 15px; cursor: pointer; }

/* 3. Navigation Icons & Dropdowns */
.header-right { display: flex; align-items: center; gap: 20px; }
.nav-icon-link { display: flex; align-items: center; text-decoration: none; color: white; }

.user-dropdown { position: relative; }
.user-trigger { background: none; border: none; color: white; display: flex; align-items: center; gap: 5px; cursor: pointer; padding: 5px 0; }
.dropdown-content { 
    display: none; position: absolute; top: 100%; right: 0; 
    background: white; min-width: 160px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); 
    border-radius: 4px; z-index: 1001;
}
.dropdown-content a { display: block; padding: 12px; color: #333; text-decoration: none; font-size: 0.9rem; }
.dropdown-content a:hover { background: #f1f1f1; }
.user-dropdown:hover .dropdown-content { display: block; }

/* 4. Category Bar (The fix for your scrolling bar) */
.category-nav {
    background: #34495e;
    display: flex;
    justify-content: center;
    gap: 30px;
    padding: 10px 0;
    position: sticky; /* Keeps it at the top while scrolling page */
    top: 0;
    z-index: 999;
}

.nav-item { position: relative; }
.cat-link { color: white; text-decoration: none; font-weight: 500; font-size: 0.95rem; padding: 10px 0; display: block; }

.mega-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: #ffffff;
    min-width: 150px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 0 0 4px 4px;
}
.mega-menu a { display: block; padding: 10px 15px; color: #333; text-decoration: none; font-size: 0.9rem; }
.mega-menu a:hover { background: #3498db; color: white; }

.nav-item:hover .mega-menu { display: block; }
.nav-item:hover .cat-link { color: #3498db; }

/* 5. Mobile Adjustments */
@media (max-width: 768px) {
    .header-center { display: none; }
    .category-nav { overflow-x: auto; justify-content: flex-start; padding: 10px 20px; }
}
</style>