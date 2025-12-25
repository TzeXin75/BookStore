<?php
// 1. SESSION START
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once 'config/db_connect.php';
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



// --- TOP 5 SELLING PRODUCTS ---
$topSellingStmt = $pdo->query("
    SELECT b.*, SUM(od.quantity) as total_sold
    FROM book b
    JOIN order_details od ON b.id = od.id
    GROUP BY b.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$topSellingBooks = $topSellingStmt->fetchAll(PDO::FETCH_ASSOC); 

// --- HELPER: DEFAULT IMAGE ---
function getBookImage($book) {
    global $defaultBookImage;
    if (!empty($book['images'])) {
        $images = explode(',', $book['images']);
        $path = "uploads/" . $images[0];
        if (file_exists($path)) return htmlspecialchars($path);
    }
    return $defaultBookImage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SIX SEVEN BS</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <!-- Swiper Section -->
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

        <!-- TOP 5 BEST SELLERS SECTION -->
        <section class="product-section bestsellers-section">
            <div class="section-header">
                <h2 class="section-title">🔥 Top 5 Best Sellers</h2>
            </div>
            <div class="product-container bestsellers-container">
                <?php if (count($topSellingBooks) > 0): ?>
                    <?php foreach ($topSellingBooks as $book): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $book['id'] ?>">
                                <img src="<?= getBookImage($book) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                            </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="price">RM<?= number_format($book['price'], 2) ?></p>
                            <h5 style="font-size: 0.9rem; color: #e74c3c; font-weight: bold;">Sold: <?= $book['total_sold'] ?></h5>
                            <?php if ($book['stock'] > 0): ?>
                                <a href="add_to_cart.php?id=<?= $book['id']; ?>" class="add-to-cart-btn" 
                            style="display:inline-block; background:#2563eb; color:white; padding:10px; text-decoration:none; border-radius:5px; font-weight:bold; font-size:0.85rem;">
                            Add to Cart
                            </a>
                            <?php else: ?>
                                <p style="color:red;">Out of Stock</p>
                            <?php endif; ?>
                            
                        </div>
                    <?php endforeach; ?>
           
                <?php else: ?>
                    <p style="padding: 20px;">No bestsellers yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- 1. FEATURED SECTION -->
        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Featured: <?= $featuredSubcategory ?></h2>
                <?php if (count($featuredBooks) > 0): ?>
                    <a href="category.php?sub=<?= urlencode($featuredSubcategory) ?>" class="view-all-btn">View All &rarr;</a>
                <?php endif; ?>
            </div>
            
            <div class="product-container">
                <?php if (count($featuredBooks) > 0): ?>
                    <?php foreach ($featuredBooks as $book): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $book['id'] ?>">
                                <img src="<?= getBookImage($book) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                            </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="price">RM<?= number_format($book['price'], 2) ?></p>
                            <a href="add_to_cart.php?id=<?= $book['id']; ?>" class="add-to-cart-btn" 
                            style="display:inline-block; background:#2563eb; color:white; padding:10px; text-decoration:none; border-radius:5px; font-weight:bold; font-size:0.85rem;">
                            Add to Cart
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="width: 100%; padding: 40px; text-align: center; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ddd;">
                        <p style="color: #666; font-size: 1rem;">No featured books available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Trending Comics</h2>
                <?php if (count($comics) > 0): ?>
                    <a href="category.php?sub=Comic" class="view-all-btn">View All &rarr;</a>
                <?php endif; ?>
            </div>
            <div class="product-container">
                <?php if (count($comics) > 0): ?>
                    <?php foreach ($comics as $book): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $book['id'] ?>">
                                <img src="<?= getBookImage($book) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                            </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="price">RM<?= number_format($book['price'], 2) ?></p>
                            <a href="add_to_cart.php?id=<?= $book['id']; ?>" class="add-to-cart-btn" 
                            style="display:inline-block; background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:4px; font-weight:bold;">
                            Add to Cart
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="width: 100%; padding: 40px; text-align: center; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ddd;">
                        <p style="color: #666; font-size: 1rem;">Coming soon! We are restacking our comics shelf.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Education & Textbooks</h2>
                <?php if (count($education) > 0): ?>
                    <a href="category.php?sub=Textbook" class="view-all-btn">View All &rarr;</a>
                <?php endif; ?>
            </div>
            <div class="product-container">
                <?php if (count($education) > 0): ?>
                    <?php foreach ($education as $book): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $book['id'] ?>">
                                <img src="<?= getBookImage($book) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                            </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="price">RM<?= number_format($book['price'], 2) ?></p>
                            <a href="add_to_cart.php?id=<?= $book['id']; ?>" class="add-to-cart-btn" 
                            style="display:inline-block; background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:4px; font-weight:bold;">
                            Add to Cart
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="width: 100%; padding: 40px; text-align: center; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ddd;">
                        <p style="color: #666; font-size: 1rem;">No education books found in database.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div id="footer-placeholder"></div>

    <script>
    fetch('footer.html')
    .then(r => r.text())
    .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });

    $(document).ready(function() {
        $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="script.js"></script>

    
</body>
</html>