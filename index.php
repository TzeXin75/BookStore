<?php
// 1. SESSION START (Must be first)
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once 'config/db_connect.php';

// --- 1. LOGIC TO PICK A RANDOM SUBCATEGORY ---
$stmtSub = $pdo->query("SELECT DISTINCT subcategory FROM book");
$allSubcategories = $stmtSub->fetchAll(PDO::FETCH_COLUMN);

if (!empty($allSubcategories)) {
    $randomSubKey = array_rand($allSubcategories);
    $featuredSubcategory = $allSubcategories[$randomSubKey];
} else {
    $featuredSubcategory = 'Novel'; 
}

// --- 2. FETCH BOOKS FOR THAT RANDOM SUBCATEGORY ---
// Increase LIMIT if you want to see more books in the Featured section
$stmt = $pdo->prepare("SELECT * FROM book WHERE subcategory = ? ORDER BY RAND() LIMIT 8");
$stmt->execute([$featuredSubcategory]);
$featuredBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- 3. HELPER FOR OTHER SECTIONS ---
function getBooks($pdo, $column, $value, $limit = 4) {
    $stmt = $pdo->prepare("SELECT * FROM book WHERE $column = ? ORDER BY id DESC LIMIT $limit");
    $stmt->execute([$value]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$comics = getBooks($pdo, 'subcategory', 'Comic', 4);
$education = getBooks($pdo, 'category', 'Education', 4); 

// --- HELPER: DEFAULT IMAGE ---
function getBookImage($book) {
    $defaultImage = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23f3f4f6%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2240%25%22%20font-size%3D%2250%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3E%F0%9F%93%96%3C%2Ftext%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2260%25%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-size%3D%2220%22%20fill%3D%22%23555%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3EBookstore%3C%2Ftext%3E%3C%2Fsvg%3E";

    if (!empty($book['images'])) {
        $images = explode(',', $book['images']);
        $path = "uploads/" . trim($images[0]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bookstore</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include 'header.php'; ?>
    
    <main>
        <!-- Swiper Section remains the same -->
        <section class="promo-swiper">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1519682337058-a94d519337bc?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80" class="slide-bg" alt="Summer Reading">
                        <div class="carousel-card">
                            <h2>Big Summer Sale!</h2>
                            <p>Up to 50% off on selected books.</p>
                            <button>Shop Now</button>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </section>

        <!-- 1. FEATURED SECTION -->
        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Featured: <?= htmlspecialchars($featuredSubcategory) ?></h2>
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
                        <p>Stock: <?= $book['stock']; ?></p>
                        <a href="add_to_cart.php?id=<?= $book['id']; ?>" class="add-to-cart-btn" style="display:inline-block; background:#2c3e50; color:white; padding:10px 20px; text-decoration:none; border-radius:4px;">Add to Cart</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 2. COMICS SECTION -->
        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Trending Comics</h2>
                <a href="category.php?sub=Comic" class="view-all-btn">View All &rarr;</a>
            </div>
            <div class="product-container">
                <?php foreach ($comics as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookImage($book) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                        </a>
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="price">$<?= number_format($book['price'], 2) ?></p>
                        <a href="add_to_cart.php?id=<?= $book['id']; ?>" class="add-to-cart-btn" style="display:inline-block; background:#2c3e50; color:white; padding:10px 20px; text-decoration:none; border-radius:4px;">Add to Cart</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 3. EDUCATION SECTION -->
        <section class="product-section">
            <div class="section-header">
                <h2 class="section-title">Education & Textbooks</h2>
                <a href="category.php?sub=Textbook" class="view-all-btn">View All &rarr;</a>
            </div>
            <div class="product-container">
                <!-- FIXED: Now correctly uses $education variable instead of $comics -->
                <?php if (count($education) > 0): ?>
                    <?php foreach ($education as $book): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $book['id'] ?>">
                                <img src="<?= getBookImage($book) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                            </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="price">$<?= number_format($book['price'], 2) ?></p>
                            <a href="add_to_cart.php?id=<?= $book['id']; ?>" class="add-to-cart-btn" style="display:inline-block; background:#2c3e50; color:white; padding:10px 20px; text-decoration:none; border-radius:4px;">Add to Cart</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="padding: 20px;">No education books found in database.</p>
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