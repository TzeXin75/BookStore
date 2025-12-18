<?php
require_once 'db.php';

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
$stmt = $pdo->prepare("SELECT * FROM book WHERE subcategory = ? ORDER BY RAND() LIMIT 4");
$stmt->execute([$featuredSubcategory]);
$featuredBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- 3. HELPER FOR OTHER SECTIONS ---
function getBooks($pdo, $column, $value) {
    $stmt = $pdo->prepare("SELECT * FROM book WHERE $column = ? ORDER BY id DESC LIMIT 4");
    $stmt->execute([$value]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$comics = getBooks($pdo, 'subcategory', 'Comic');
$education = getBooks($pdo, 'category', 'Education'); 

// --- HELPER: DEFAULT IMAGE ---
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

                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1495446815901-a7297e633e8d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80" class="slide-bg" alt="Books Background">
                        <div class="carousel-card">
                            <h2>New Arrivals</h2>
                            <p>Check out the latest books this month.</p>
                            <button>Discover</button>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80" class="slide-bg" alt="Cozy Reading">
                        <div class="carousel-card">
                            <h2>Free Shipping Weekend</h2>
                            <p>Get free shipping on orders above $50.</p>
                            <button>Grab Offer</button>
                        </div>
                    </div>

                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </section>
    </main>

    <section class="product-section">
        <div class="section-header">
            <h2 class="section-title">Featured Books</h2>
            <a href="category.php?sub=<?= urlencode($featuredSubcategory) ?>" class="view-all-btn">View All &rarr;</a>
        </div>
        
        <div class="product-container">
            <?php if (count($featuredBooks) > 0): ?>
                <?php foreach ($featuredBooks as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookImage($book) ?>" 
                             alt="<?= htmlspecialchars($book['title']) ?>"
                             onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                        </a>
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="price">$<?= number_format($book['price'], 2) ?></p>
                        <?php if ($book['stock'] > 0): ?>
                            <p>Stock: <?php echo $book['stock']; ?></p>

                            <button><a href="add_to_cart.php?id=<?php echo $book['id']; ?>" >Add to Cart</a></button>
                        <?php else: ?>
                            <p style="color:red;">Out of Stock</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No featured books available.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="about-section">
            <div class="about-container">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="About Bookstore">
                </div>
                <div class="about-content">
                    <h2>About Our Bookstore</h2>
                    <p>At <strong>BookStore</strong>, we believe that every book has the power to change lives. Founded in 2020, we’ve been connecting readers with stories that inspire.</p>
                    <button>Read More</button>
                </div>
            </div>
        </section>

    <section class="product-section">
        <div class="section-header">
            <h2 class="section-title">Trending Comics</h2>
            <a href="category.php?sub=comic" class="view-all-btn">View All &rarr;</a>
        </div>

        <div class="product-container">
            <?php if (count($comics) > 0): ?>
                <?php foreach ($comics as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookImage($book) ?>" 
                             alt="<?= htmlspecialchars($book['title']) ?>"
                             onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                        </a>
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="price">$<?= number_format($book['price'], 2) ?></p>
                        <?php if ($book['stock'] > 0): ?>
                            <p>Stock: <?php echo $book['stock']; ?></p>

                            <button><a href="add_to_cart.php?id=<?php echo $book['id']; ?>" >Add to Cart</a></button>
                        <?php else: ?>
                            <p style="color:red;">Out of Stock</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comics available.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="news-section">
        <h2 class="section-title">News & Features</h2>
        <div class="news-container">
            <div class="news-card">
                <img src="https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Book Signing">
                <div class="news-content">
                    <h3>Book Signing Event with Sarah Bloom</h3>
                    <p>Join us this weekend for an exclusive signing session.</p>
                    <button>Read More</button>
                </div>
            </div>
            <div class="news-card">
                <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="New Store">
                <div class="news-content">
                    <h3>New Store Opening in Downtown</h3>
                    <p>We’re excited to announce our new bookstore opening soon!</p>
                    <button>Learn More</button>
                </div>
            </div>
            <div class="news-card">
                <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Reading Challenge">
                <div class="news-content">
                    <h3>Summer Reading Challenge</h3>
                    <p>Complete our challenge and win free books.</p>
                    <button>Join Now</button>
                </div>
            </div>
            <div class="news-card">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Book Club">
                <div class="news-content">
                    <h3>Online Book Club</h3>
                    <p>Connect with readers worldwide in our monthly virtual meetups.</p>
                    <button>Join Now</button>
                </div>
            </div>
        </div>
    </section>

        <section class="product-section">
        <div class="section-header">
            <h2 class="section-title">Education & Textbooks</h2>
            <a href="category.php?sub=textbook" class="view-all-btn">View All &rarr;</a>
        </div>

        <div class="product-container">
            <?php if (count($comics) > 0) : ?>
                <?php foreach ($comics as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookImage($book) ?>" 
                             alt="<?= htmlspecialchars($book['title']) ?>"
                             onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                        </a>
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="price">$<?= number_format($book['price'], 2) ?></p>
                        <?php if ($book['stock'] > 0): ?>
                            <p>Stock: <?php echo $book['stock']; ?></p>

                            <button><a href="add_to_cart.php?id=<?php echo $book['id']; ?>" >Add to Cart</a></button>
                        <?php else: ?>
                            <p style="color:red;">Out of Stock</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No education books available.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="about-section">
        <div class="about-container">
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Member Promotion">
            </div>
            <div class="about-content">
                <h2>Exclusive Member Promotions</h2>
                <p>
                    Join our <strong>BookStore Membership Program</strong> and enjoy amazing perks! 
                    Members receive early access to new arrivals, exclusive discounts, and special event invitations.
                </p>
                <button>Join Now</button>
            </div>
        </div>
    </section>

    

    <div id="footer-placeholder"></div>

    <script>
    fetch('header.php')
    .then(r => r.text())
    .then(data => {
        document.getElementById('header-placeholder').innerHTML = data;
        $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); });
        $('.nav-item').hover(
            function() { if ($(window).width() > 768) $(this).children('.sub-menu').stop(true, true).slideDown(200); },
            function() { if ($(window).width() > 768) $(this).children('.sub-menu').stop(true, true).slideUp(200); }
        );
        $('.main-category').click(function(e) {
            if ($(window).width() <= 768) {
                e.preventDefault();
                $(this).siblings('.sub-menu').stop(true, true).slideToggle(200);
            }
        });
    });

    fetch('footer.html')
    .then(r => r.text())
    .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>