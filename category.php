<?php
require_once 'db.php';

// --- 0. SETUP DEFAULT IMAGE ---
// If no book image is found, this file will be shown instead
$defaultBookImage = "uploads/download.svg";

// --- 1. SETTINGS & GETTING URL DATA ---
// Get the subcategory (like 'novel') from the URL, or use 'novel' as the default
$subcategory = isset($_GET['sub']) ? $_GET['sub'] : 'novel';

// Pagination: Show only 12 books per page
$limit = 12;
// Get current page number; if not set, start at page 1
$page_no = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
// Calculate where to start the list in the database (e.g., Page 2 starts at book 13)
$offset = ($page_no - 1) * $limit;

// Get search words and sorting preference from the URL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_desc';

// --- 2. DATABASE SEARCH LOGIC ---
// Start building the SQL command to find books in the correct subcategory
$where = "WHERE subcategory = :subcategory";
$params = ['subcategory' => $subcategory];

// If the user typed something in the search bar, add a "LIKE" filter to the SQL
if ($search) {
    $where .= " AND (title LIKE :search OR author LIKE :search OR publisher LIKE :search)";
    $params['search'] = "%$search%";
}

// First, count total books to figure out how many page numbers we need
$stmt = $pdo->prepare("SELECT COUNT(*) FROM book $where");
$stmt->execute($params);
$total_books = $stmt->fetchColumn();
$total_pages = ceil($total_books / $limit);

// Second, fetch the actual book records for the current page
$sql = "SELECT * FROM book $where";
// Add the "ORDER BY" part based on what the user picked in the Sort dropdown
switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    case 'title_asc': $sql .= " ORDER BY title ASC"; break;
    case 'title_desc': $sql .= " ORDER BY title DESC"; break;
    default: $sql .= " ORDER BY id DESC"; // Newest first
}
// Add the Limit and Offset to only get 12 books for this specific page
$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 3. HELPER FUNCTION: CHOOSE IMAGE ---
// This decides which image to show for a book
function getBookImage($book) {
    global $defaultBookImage; 

    // Step 1: Try to show the "Main Cover" image
    if (!empty($book['cover_image'])) {
        $coverPath = "uploads/" . trim($book['cover_image']);
        if (file_exists($coverPath)) return htmlspecialchars($coverPath);
    }

    // Step 2: If no cover, try to use the first image from the gallery
    if (!empty($book['images'])) {
        $images = explode(',', $book['images']);
        $galleryPath = "uploads/" . trim($images[0]);
        if (file_exists($galleryPath)) return htmlspecialchars($galleryPath);
    }

    // Step 3: If both are missing, use the default bookstore icon
    return $defaultBookImage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($subcategory) ?></title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

 <?php include 'header.php'; ?>

<main>
    <div class="product-page">
        <div class="product-header">
            <h2><?= ucfirst($subcategory) ?></h2>
            <p>Showing <?= count($books) ?> results</p>
        </div>

    <div class="filter-container">
    <form method="GET" class="search-form" style="width:100%;">
        <input type="hidden" name="sub" value="<?= htmlspecialchars($subcategory) ?>">
        
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        
        <select name="sort">
            <option value="id_desc" <?= $sort==='id_desc' ? 'selected' : '' ?>>Newest</option>
            <option value="price_asc" <?= $sort==='price_asc' ? 'selected' : '' ?>>Price: Low → High</option>
            <option value="price_desc" <?= $sort==='price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
            <option value="title_asc" <?= $sort==='title_asc' ? 'selected' : '' ?>>Title A → Z</option>
            <option value="title_desc" <?= $sort==='title_desc' ? 'selected' : '' ?>>Title Z → A</option>
        </select>
        
        <button type="submit">Filter</button>
    </form>
    </div>

        <div class="product-grid">
            <?php if ($books): ?>
                <?php foreach ($books as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookImage($book) ?>" 
                                 alt="<?= htmlspecialchars($book['title']) ?>"
                                 onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                        </a>
                        
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p>Author: <?= htmlspecialchars($book['author']) ?></p>
                        <p class="price">$<?= number_format($book['price'], 2) ?></p>
                        <button>Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="width:100%; text-align:center;">No books found.</p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?sub=<?= urlencode($subcategory) ?>&p=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                   class="<?= $i==$page_no?'active':'' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>

    <div id="footer-placeholder"></div>
</main>

<script>
fetch('header.php').then(r=>r.text()).then(data=>{
    document.getElementById('header-placeholder').innerHTML = data;
    // Mobile menu toggle
    $('#hamburger').click(()=>$('#navLinks').toggleClass('active'));
    // Desktop hover menus
    $('.nav-item').hover(
        function(){ if($(window).width()>768) $(this).children('.sub-menu').stop(true,true).slideDown(200); },
        function(){ if($(window).width()>768) $(this).children('.sub-menu').stop(true,true).slideUp(200); }
    );
});

fetch('footer.html').then(r => r.text()).then(data => { 
        document.getElementById('footer-placeholder').innerHTML = data; 
    });
</script>

</body>
</html>