<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

// Define the default placeholder
$defaultBookImage = "uploads/download.svg";

if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
    $_user = $_SESSION['user']; 
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $_user = ['username' => 'Member']; 
}

$query = $_GET['q'] ?? '';
$sort = $_GET['sort'] ?? 'id_desc';

// --- PAGINATION CONFIGURATION ---
$limit = 12; // 6 books per row * 2 rows
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=0"; // Default to no results if no query
$params = [];

if (!empty($query)) {
    $where = "WHERE title LIKE :search OR author LIKE :search OR publisher LIKE :search";
    $params['search'] = "%$query%";
}

// Count total records for pagination
$countSql = "SELECT COUNT(*) FROM book $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalBooks = $countStmt->fetchColumn();
$totalPages = ceil($totalBooks / $limit);

// Fetch books with LIMIT and OFFSET
$sql = "SELECT * FROM book $where";
switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    default: $sql .= " ORDER BY id DESC";
}
$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- IMAGE HELPER FUNCTION ---
function getBookDisplayImage($book) {
    global $defaultBookImage;
    if (!empty($book['cover_image'])) {
        $coverPath = "uploads/" . trim($book['cover_image']);
        if (file_exists($coverPath)) return $coverPath;
    }
    if (!empty($book['images'])) {
        $imgs = explode(',', $book['images']);
        if (!empty($imgs[0])) {
            $galleryPath = "uploads/" . trim($imgs[0]);
            if (file_exists($galleryPath)) return $galleryPath;
        }
    }
    return $defaultBookImage;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Search Results: <?= htmlspecialchars($query) ?></title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    /* --- EXACT CSS FROM CATEGORY.PHP --- */
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

        .product-container {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 20px !important;
            justify-content: flex-start !important;
            width: 100% !important;
        }

        .product-card {
            flex: 0 0 160px !important; 
            width: 160px !important;
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

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
            padding-bottom: 40px;
        }

        .pagination a {
            padding: 8px 16px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #2c3e50;
            border-radius: 4px;
            transition: 0.3s;
        }

        .pagination a.active {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .pagination a:hover:not(.active) {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div id="header-placeholder"></div>

    <main class="product-section">
        <div class="section-header">
            <h2 class="section-title">
                <?php if ($query): ?>
                    Results for "<?= htmlspecialchars($query) ?>"
                <?php else: ?>
                    Search
                <?php endif; ?>
            </h2>
        </div>

        <div class="product-container">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookDisplayImage($book) ?>" 
                                 alt="<?= htmlspecialchars($book['title']) ?>"
                                 onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                        </a>
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="price">RM <?= number_format($book['price'], 2) ?></p>
                        <p style="font-size: 0.85rem; color: #666; margin-bottom: 8px;">Stock: <?= $book['stock'] ?></p>
                        
                        <div style="margin-top: auto;">
                            <a href="add_to_cart.php?id=<?= $book['id'] ?>" style="display: block; background: #2563eb; color: white; padding: 10px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.9rem; text-align: center;">Add to Cart</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; width: 100%; padding: 50px;">
                    <p>No results found for "<?= htmlspecialchars($query) ?>".</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?q=<?= urlencode($query) ?>&p=<?= $page-1 ?>">&laquo; Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?q=<?= urlencode($query) ?>&p=<?= $i ?>" 
                   class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?q=<?= urlencode($query) ?>&p=<?= $page+1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <div id="footer-placeholder"></div>

    <script>
        // Load Header and Footer
        fetch('header.php').then(r => r.text()).then(data => { document.getElementById('header-placeholder').innerHTML = data; });
        fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });

        $(document).ready(function() {
            $(document).on('click', '#hamburger', function() { $('#navLinks').toggleClass('active'); });
        });
    </script>
</body>
</html>