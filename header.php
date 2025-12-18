<?php require '_base.php'; ?>

<header>
    <div class="header-container">
        <div class="header-left">
            <a href="index.php" class="logo">BookStore</a>
        </div>

        <div class="header-center">
            <form class="search-form" action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search by title, author, publisher..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="header-right">
            <!-- User Account Section -->
            <div class="nav-item user-menu">
                <?php if (isset($_user)): ?>
                    <a href="profile.php" class="account-btn">
                        <i class="fas fa-user"></i>
                        <span class="username"><?= htmlspecialchars($_user->username) ?></span>
                    </a>
                    <div class="sub-menu">
                        <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="account-btn">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Cart -->
            <a href="cart.php" class="cart-btn">
                <i class="fas fa-shopping-cart"></i>
            </a>

            <!-- Orders -->
            <a href="my_orders.php" class="order-btn">
                <i class="fas fa-history"></i>
            </a>
        </div>

        <button class="hamburger" id="hamburger">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <nav class="nav-links" id="navLinks">
        <div class="nav-item">
            <a href="#" class="main-category">Fiction</a>
            <div class="sub-menu">
                <a href="category.php?sub=Novel">Novel</a>
                <a href="category.php?sub=Comic">Comic</a>
            </div>
        </div>

        <div class="nav-item">
            <a href="#" class="main-category">Non-Fiction</a>
            <div class="sub-menu">
                <a href="category.php?sub=Biography">Biography</a>
                <a href="category.php?sub=Self-help">Self-help</a> 
            </div>
        </div>

        <div class="nav-item">
            <a href="#" class="main-category">Children</a>
            <div class="sub-menu">
                <a href="category.php?sub=Color Book">Color book</a>
            </div>
        </div>

        <div class="nav-item">
            <a href="#" class="main-category">Education</a>
            <div class="sub-menu">
                <a href="category.php?sub=Textbook">Textbook</a>
            </div>
        </div>
    </nav>
</header>

<script>
    // Toggle Mobile Menu
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');

    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // Optional: Close mobile menu when clicking a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
        });
    });
</script>

<style>
    /* Enhance user menu dropdown */
    .user-menu {
        position: relative;
    }

    .user-menu .account-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
        font-weight: 500;
    }

    .user-menu .account-btn .username {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .user-menu .sub-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background: #2c3e50;
        min-width: 180px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        border-radius: 6px;
        overflow: hidden;
        z-index: 1000;
    }

    .user-menu:hover .sub-menu {
        display: block;
    }

    .user-menu .sub-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: white;
        font-size: 0.95rem;
    }

    .user-menu .sub-menu a:hover {
        background: #3498db;
    }

    /* Mobile adjustment */
    @media (max-width: 768px) {
        .user-menu .sub-menu {
            position: static;
            display: none;
            background: #34495e;
            box-shadow: none;
        }

        .user-menu:hover .sub-menu,
        .nav-links.active .user-menu .sub-menu {
            display: block;
        }

        .username {
            display: none; /* Hide username on mobile for space */
        }
    }
</style>