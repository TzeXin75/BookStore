<?php
require_once 'db.php';

// 1. Get the search query from the URL (header uses name="q")
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// 2. Prepare the Search Logic
$books = [];
if ($query) {
    // Search in Title, Author, or Publisher
    $sql = "SELECT * FROM book WHERE title LIKE :q OR author LIKE :q OR publisher LIKE :q ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['q' => "%$query%"]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper for default images 
function getBookImage($book) {
    $defaultImage = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23f3f4f6%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2240%25%22%20font-size%3D%2250%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3E%F0%9F%93%96%3C%2Ftext%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2260%25%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-size%3D%2220%22%20fill%3D%22%23555%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3EBookstore%3C%2Ftext%3E%3C%2Fsvg%3E";

    if (!empty($book['images'])) {
        $images = explode(',', $book['images']);
        $path = "uploads/" . $images[0];
        if (file_exists($path)) return htmlspecialchars($path);
    }
    return $defaultImage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?= htmlspecialchars($query) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    
    <style>
        .product-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 5px;
        }
        .no-results {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 1.2rem;
        }

        .product-search {
        display: flex !important;
        grid-template-columns: repeat(4, 1fr) !important; /* Home page is 4 columns */
        gap: 20px;
        width: 100%;
        justify-content: flex-start;
    }

    .product-search .product-card {
        background: white; border: 1px solid #e0e0e0; border-radius: 8px;
        padding: 20px; text-align: center; transition: transform 0.2s, box-shadow 0.2s;
        display: flex; flex-direction: column; 
        margin-bottom: 0;
        margin-top: 20px;
    }
    </style>
</head>
<body>

    <div id="header-placeholder"></div>

    <main>
        <div class="product-section">
            <h2 class="section-title">
                <?php if ($query): ?>
                    Search Results for "<?= htmlspecialchars($query) ?>"
                <?php else: ?>
                    Please enter a search term
                <?php endif; ?>
            </h2>

            <?php if ($books): ?>
                <div class="product-search">
                    <?php foreach ($books as $book): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookImage($book) ?>" 
                             alt="<?= htmlspecialchars($book['title']) ?>"
                             onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                        </a>
                            <h3><?= htmlspecialchars($book['title']) ?></h3>
                            <p>by <?= htmlspecialchars($book['author']) ?></p>
                            <p class="price">$<?= number_format($book['price'], 2) ?></p>
                            <button>Add to Cart</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px; color: #ddd;"></i>
                    <p>We couldn't find any books matching <bold><?= htmlspecialchars($query) ?></bold>.</p>
            <?php endif; ?>
        </div>
    </main>

    <div id="footer-placeholder"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    fetch('header.php').then(r=>r.text()).then(data=>{
        document.getElementById('header-placeholder').innerHTML = data;
        $('#hamburger').click(()=>$('#navLinks').toggleClass('active'));
        $('.nav-item').hover(
            function(){ if($(window).width()>768) $(this).children('.sub-menu').stop(true,true).slideDown(200); },
            function(){ if($(window).width()>768) $(this).children('.sub-menu').stop(true,true).slideUp(200); }
        );
        $('.main-category').click(function(e){
            if($(window).width()<=768){ e.preventDefault(); $(this).siblings('.sub-menu').stop(true,true).slideToggle(200); }
        });
    });

    fetch('footer.html').then(r=>r.text()).then(data=>{
        document.getElementById('footer-placeholder').innerHTML = data;
    });
    </script>
</body>
</html>