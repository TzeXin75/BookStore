<?php
require_once 'db.php';
$defaultBookImage = "uploads/download.svg";

// 1. Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// 2. Search Logic
$books = [];
if ($query) {
    $sql = "SELECT * FROM book WHERE title LIKE :q OR author LIKE :q OR publisher LIKE :q ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['q' => "%$query%"]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper for images
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?= htmlspecialchars($query) ?></title>
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Center the search content */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .section-title {
            margin-bottom: 30px;
            font-size: 1.8rem;
            color: #333;
        }

        /* FLEXIBLE GRID: This prevents cards from becoming too wide */
        .product-search {
            display: grid !important;
            /* Creates 4 columns, but reduces them automatically on smaller screens */
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
            gap: 25px;
            width: 100%;
        }

        .product-search .product-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .product-search .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        /* IMAGE FIX: This keeps the book covers elegant and sized correctly */
        .product-card img {
            width: 100%;
            height: 280px; /* Fixed height for consistency */
            object-fit: contain; /* Shows the whole book cover without stretching */
            border-radius: 4px;
            margin-bottom: 15px;
            background-color: #f8f9fa; /* Light grey instead of bright blue */
        }

        .product-card h3 {
            font-size: 1.1rem;
            margin: 10px 0 5px;
            color: #2c3e50;
            /* Ensure titles only take up 2 lines for a clean look */
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.4em;
        }

        .product-card p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .product-card .price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #304458;
            margin-bottom: 15px;
        }

        .product-card button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: auto; /* Keeps button at the bottom */
            font-weight: bold;
        }

        .product-card button:hover {
            background: #415a77;
        }

        .no-results {
            text-align: center;
            padding: 80px 20px;
            color: #95a5a6;
        }
    </style>
</head>
<body>

    <div id="header-placeholder"></div>

    <main>
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
                        <p class="price">RM<?= number_format($book['price'], 2) ?></p>
                        <button>Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p style="font-size: 4rem; margin-bottom: 10px;">🔍</p>
                <p>We couldn't find any books matching <strong>"<?= htmlspecialchars($query) ?>"</strong>.</p>
            </div>
        <?php endif; ?>
    </main>

    <div id="footer-placeholder"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Header & Footer fetching logic
    fetch('header.php').then(r=>r.text()).then(data=>{
        document.getElementById('header-placeholder').innerHTML = data;
        $('#hamburger').click(()=>$('#navLinks').toggleClass('active'));
    });

    fetch('footer.html').then(r=>r.text()).then(data=>{
        document.getElementById('footer-placeholder').innerHTML = data;
    });
    </script>
</body>
</html>