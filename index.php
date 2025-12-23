<?php
require_once 'db.php';
$defaultBookImage = "uploads/download.svg";

// --- 1. PICK A RANDOM CATEGORY TO SHOW ---
// This finds all subcategories in database and picks one by chance
$stmtSub = $pdo->query("SELECT DISTINCT subcategory FROM book");
$allSubcategories = $stmtSub->fetchAll(PDO::FETCH_COLUMN);

if (!empty($allSubcategories)) {
    $randomSubKey = array_rand($allSubcategories);
    $featuredSubcategory = $allSubcategories[$randomSubKey];
} else {
    $featuredSubcategory = 'Novel'; 
}

// --- 2. GET 5 RANDOM BOOKS FROM THAT CATEGORY ---
// This makes the home page look different and fresh every time it is refreshed
// Changed LIMIT to 5 to match our new balanced grid width
$stmt = $pdo->prepare("SELECT * FROM book WHERE subcategory = ? ORDER BY RAND() LIMIT 5");
$stmt->execute([$featuredSubcategory]);
$featuredBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- 3. HELPER FOR SPECIFIC SECTIONS ---
// This function easier to grab books for sections like "Comics" or "Education"
function getBooks($pdo, $column, $value) {
    $stmt = $pdo->prepare("SELECT * FROM book WHERE $column = ? ORDER BY id DESC LIMIT 5");
    $stmt->execute([$value]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$comics = getBooks($pdo, 'subcategory', 'Comic');
$education = getBooks($pdo, 'category', 'Education'); 

// --- 4. IMAGE HELPER ---
// Decides which image to show: User upload, or a default SVG icon
function getBookImage($book) {
    $defaultImage = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23f3f4f6%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2240%25%22%20font-size%3D%2250%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3E%F0%9F%93%96%3C%2Ftext%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2260%25%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-size%3D%2220%22%20fill%3D%22%23555%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3EBookstore%3C%2Ftext%3E%3C%2Fsvg%3E";

    if (!empty($book['images'])) {
        $images = explode(',', $book['images']);
        $path = "uploads/" . $images[0];
        if (file_exists($path)) {
            return htmlspecialchars($path);
        }
    }
    return $defaultImage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Bookstore Home</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <style>
        /* --- GLOBAL SECTION STYLING --- */
        .product-section {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-title { font-size: 1.5rem; font-weight: bold; color: #2c3e50; }

        /* --- THE BALANCED FLEX GRID (FIXED NO BULKY) --- */
        .product-container {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 20px !important;
            justify-content: flex-start !important;
            width: 100% !important;
        }

        /* --- BALANCED PRODUCT CARDS (190px WIDTH) --- */
        .product-card {
            flex: 0 0 190px !important;
            width: 190px !important;
            background: white !important;
            border: 1px solid #eee !important;
            border-radius: 10px !important;
            padding: 12px !important;
            display: flex !important;
            flex-direction: column !important;
            height: 380px !important;
            text-align: center !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 6px 15px rgba(0,0,0,0.1); 
        }

        .product-card img {
            width: 100% !important;
            height: 200px !important;
            object-fit: contain !important;
            background-color: #f8f9fa !important;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .product-card h3 {
            font-size: 0.95rem;
            color: #333;
            margin: 8px 0;
            display: -webkit-box;
            -webkit-box-orient: vertical;  
            overflow: hidden;
            height: 2.6em;
            line-height: 1.3;
        }

        .product-card .price {
            font-weight: bold;
            color: #b03030;
            font-size: 1.05rem;
            margin-top: auto; 
            margin-bottom: 10px;
        }

        .product-card button {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: bold;
            cursor: pointer;
        }

        /* --- ABOUT & MEMBER PROMO LAYOUT (PREVENTS BIGGEST) --- */
        .promo-block { padding: 40px 0; }
        .promo-container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 40px;
            padding: 0 20px;
        }
        .promo-image { flex: 0 0 400px; } 
        .promo-image img { width: 100%; height: 280px; object-fit: cover; border-radius: 12px; }
        .promo-content { flex: 1; }

        @media (max-width: 768px) {
            .product-card { flex: 0 0 calc(50% - 10px) !important; width: calc(50% - 10px) !important; }
            .promo-container { flex-direction: column; text-align: center; }
            .promo-image { flex: 0 0 auto; width: 100%; }
        }
    </style>
</head>
<body>
  
    <?php include 'header.php'; ?>
    
    <main>
        <section class="promo-swiper">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1519682337058-a94d519337bc?auto=format&fit=crop&w=1600&q=80" class="slide-bg">
                        <div class="carousel-card">
                            <h2>Big Summer Sale!</h2>
                            <button>Shop Now</button>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=1600&q=80" class="slide-bg">
                        <div class="carousel-card">
                            <h2>New Arrivals</h2>
                            <button>Discover</button>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </section>

        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Featured: <?= $featuredSubcategory ?></h2>
                <a href="category.php?sub=<?= urlencode($featuredSubcategory) ?>" class="view-all-btn">View All &rarr;</a>
            </div>
            
            <div class="product-container">
                <?php foreach ($featuredBooks as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookImage($book) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                        </a>
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="price">$<?= number_format($book['price'], 2) ?></p>
                        <button>Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="news-section">
            <h2 class="section-title">News & Features</h2>
            <div class="news-container">
                <div class="news-card">
                    <img src="https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=500&q=60" alt="Book Signing">
                    <div class="news-content">
                        <h3>Book Signing Event with Sarah Bloom</h3>
                        <p>Join us this weekend for an exclusive signing session and meet the author.</p>
                        <button>Read More</button>
                    </div>
                </div>
                <div class="news-card">
                    <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=500&q=60" alt="New Store">
                    <div class="news-content">
                        <h3>New Store Opening in Downtown</h3>
                        <p>We’re excited to announce our new bookstore opening soon in the city center!</p>
                        <button>Learn More</button>
                    </div>
                </div>
                <div class="news-card">
                    <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=500&q=60" alt="Reading Challenge">
                    <div class="news-content">
                        <h3>Summer Reading Challenge</h3>
                        <p>Complete our 30-day challenge and win a set of limited edition bookmarks.</p>
                        <button>Join Now</button>
                    </div>
                </div>
                <div class="news-card">
                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=500&q=60" alt="Book Club">
                    <div class="news-content">
                        <h3>Online Book Club</h3>
                        <p>Connect with readers worldwide in our monthly virtual meetups and discussions.</p>
                        <button>Join Now</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Trending Comics</h2>
                <a href="category.php?sub=comic" class="view-all-btn">View All &rarr;</a>
            </div>

            <div class="product-container">
                <?php if ($comics): ?>
                    <?php foreach ($comics as $book): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $book['id'] ?>">
                                <img src="<?= getBookImage($book) ?>" 
                                    alt="<?= htmlspecialchars($book['title']) ?>"
                                    onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                            </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="price">$<?= number_format($book['price'], 2) ?></p>
                            <button>Add to Cart</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No comics available at the moment.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="promo-block">
            <div class="promo-container">
                <div class="promo-image">
                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80" alt="About Bookstore">
                </div>
                <div class="promo-content">
                    <h2>About Our Bookstore</h2>
                    <p>At <strong>BookStore</strong>, we believe that every book has the power to change lives. Founded in 2020, we’ve been connecting readers with stories that inspire and educate.</p>
                    <p>Our mission is to make reading accessible to everyone, everywhere.</p>
                    <button style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 5px;">Read More</button>
                </div>
            </div>
        </section>

        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Academic Education</h2>
                <a href="category.php?cat=Education" class="view-all-btn">View All &rarr;</a>
            </div>

            <div class="product-container">
                <?php if ($education): ?>
                    <?php foreach ($education as $book): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $book['id'] ?>">
                                <img src="<?= getBookImage($book) ?>" 
                                    alt="<?= htmlspecialchars($book['title']) ?>"
                                    onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                            </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="price">$<?= number_format($book['price'], 2) ?></p>
                            <button>Add to Cart</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No education books available at the moment.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="promo-block" style="background: #f8f9fa;">
            <div class="promo-container">
                <div class="promo-content">
                    <h2>Exclusive Member Promotions</h2>
                    <p>Join our <strong>BookStore Membership Program</strong> and enjoy amazing perks! Members receive early access to new arrivals, exclusive discounts, and special event invitations.</p>
                    <button onclick="window.location.href='login.php'" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 5px;">Join Now</button>
                </div>
                <div class="promo-image">
                    <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80" alt="Member Promotion">
                </div>
            </div>
        </section>
    </main>

    <div id="footer-placeholder"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
    var swiper = new Swiper(".mySwiper", {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: ".swiper-pagination", clickable: true },
        navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
    });

    fetch('header.php').then(r => r.text()).then(data => {
        document.getElementById('header-placeholder').innerHTML = data;
        $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); });
    });

    fetch('footer.html').then(r => r.text()).then(data => { 
        document.getElementById('footer-placeholder').innerHTML = data; 
    });
    </script>
</body>
</html>