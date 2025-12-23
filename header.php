<header>
    <div class="header-container">
        <div class="header-left">
            <a href="index.php" class="logo">BookStore</a>
        </div>

        <div class="header-center">
            <form class="search-form" action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search by title, author, publisher">
                <button type="submit" style="font-size: 1.2rem;">🔍</button>
            </form>
        </div>

        <div class="header-right">
            <a href="login.php" class="account-btn" title="Account">
                <span style="font-size: 1.5rem;color:white;">옷</span>
            </a>
            <a href="cart.php" class="cart-btn" title="Cart">
                <span style="font-size: 1.5rem;color:white; ">🛒</span>
            </a>
            <a href="timeline.php" class="timeline-btn" title="timeline">
                <span style="font-size: 1.5rem;color:white; ">◔</span>
            </a>
        </div>
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
    // 3. MOBILE MENU LOGIC
    // When the 'hamburger' icon is clicked, show or hide the menu
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');

    if(hamburger) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }
</script>