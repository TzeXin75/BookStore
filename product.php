<?php
require_once 'db.php';

// 1. GET THE ID & FETCH DATA
if (!isset($_GET['id'])) {
    header("Location: index.php"); // Redirect if no ID
    exit;
}
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM book WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) die("Book not found.");

// Process Images for the Gallery
$images = !empty($book['images']) ? explode(',', $book['images']) : ['default.png'];
$mainImage = "uploads/" . trim($images[0]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
       
        

        .book-container {
            display: grid !important;
            grid-template-columns: repeat(2,1fr) ;
            gap: 20px;
            width: 100%;
            justify-content: flex-start;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 12fr 8fr; /* Two equal columns */
            gap: 40px;
        }

        /* LEFT COLUMN: GALLERY */
        .gallery-wrapper {
            display: flex;
            gap: 15px;
           
        }
        .thumbnail-strip {
            display: flex;
            flex-direction: column; /* Vertical strip like the image */
            gap: 10px;
        }
        .thumb {
            width: 60px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 1px solid #ddd;
            opacity: 0.7;
            transition: 0.3s;
        }
        .thumb.active, .thumb:hover { opacity: 1; border-color: #333; }
        
        .main-image-area img {
            width: 100%;
            max-width: 500px;
            height: auto;
            object-fit: contain;
        }

        /* RIGHT COLUMN: INFO */
        .product-info h1 { font-size: 2rem; margin-bottom: 10px; }
        .price-range { color: #5bc0de; font-size: 1.5rem; margin-bottom: 20px; font-weight: bold; } /* Cyan color from image */
        
        .specs { margin-bottom: 20px; color: #666; font-size: 0.9rem; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        
        .description-list { padding-left: 20px; margin-bottom: 30px; line-height: 1.6; color: #555; max-height: 200px;overflow-y: auto;padding-right: 10px; }
        
        /* The "Note" Section (Red Text) */
        .shipping-note {
            margin-bottom: 30px;
        }
        .shipping-note h4 { color: #5bc0de; margin-bottom: 5px; }
        .shipping-note ul { list-style: none; padding: 0; }
        .shipping-note li { 
            color: #d9534f; /* Red color */
            font-size: 0.9rem; 
            margin-bottom: 8px;
            padding-left: 10px;
            border-left: 2px solid #d9534f;
        }

        /* ACTION AREA (Bottom Gray Box) */
        .action-box {
            background: #f9f9f9;
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .qty-row { display: flex; justify-content: space-between; align-items: center; }
        
        .add-btn {
            background-color: #304458ff;
            color: white;
            width: 100%;
            padding: 15px;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            text-transform: uppercase;
        }
        .add-btn:hover { background-color: #3e5e7cff; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="book-container">
        
        <div class="gallery-wrapper">
            <div class="thumbnail-strip">
 
                <?php foreach($images as $img): ?>
                    <img src="uploads/<?= trim($img) ?>" alt="gallery"class="thumb" onclick="changeImage(this)">
                <?php endforeach; ?>
            </div>

            <div class="main-image-area">
                
                <img id="mainImg" src="<?= $mainImage ?>" alt="<?= htmlspecialchars($book['title']) ?>"  onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
            </div>
        </div>

        <div class="product-info">
            <h1><?= htmlspecialchars($book['title']) ?></h1>
            <div class="price-range">RM<?= number_format($book['price'], 2) ?></div>
            
            <div class="specs">
                Author: <?= htmlspecialchars($book['author']) ?> | Publisher: <?= htmlspecialchars($book['publisher']) ?>
            </div>

            <ul class="description-list">
                <?php 
                $desc_points = explode("\n", $book['description']);
                foreach($desc_points as $point) {
                    if(trim($point)) echo "<li>" . htmlspecialchars($point) . "</li>";
                }
                ?>
            </ul>

            <div class="shipping-note">
                <h4>Note</h4>
                <ul>
                    <li><strong>Stock Level:</strong> <?= $book['stock'] ?> units remaining.</li>
                    <li>Orders with non-delivery addresses will be cancelled.</li>
                    <li>Images are for illustration purposes only.</li>
                </ul>
            </div>

            <form action="add_to_cart.php" method="POST" class="action-box">
                <input type="hidden" name="product_id" value="<?= $book['id'] ?>">
                
                <div class="qty-row">
                    <label><strong>Quantity</strong></label>
                    <input type="number" name="quantity" value="1" min="1" max="<?= $book['stock'] ?>" style="padding: 5px; width: 60px;">
                </div>

                <button type="submit" class="add-btn">Add to cart</button>
            </form>
        </div>
    </div>

    <script>
        // switch main image
        function changeImage(element) {
            document.getElementById('mainImg').src = element.src;
            // Handle active class styling
            document.querySelectorAll('.thumb').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
        }
    </script>

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