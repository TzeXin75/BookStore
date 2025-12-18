<?php
require_once 'db.php';

// --- 0. Define Default Image (Bookstore SVG ICON) ---

$defaultBookImage = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23f3f4f6%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2240%25%22%20font-size%3D%2250%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3E%F0%9F%93%96%3C%2Ftext%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2260%25%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-size%3D%2220%22%20fill%3D%22%23555%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3EBookstore%3C%2Ftext%3E%3C%2Fsvg%3E";

// --- 1. UNIVERSAL CATEGORY LOGIC ---
$subcategory = isset($_GET['sub']) ? $_GET['sub'] : 'novel';

// Pagination settings
$limit = 12;
$page_no = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page_no - 1) * $limit;

// Search and sorting
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_desc';

// --- DATABASE QUERY ---
$where = "WHERE subcategory = :subcategory";
$params = ['subcategory' => $subcategory];

if ($search) {
    $where .= " AND (title LIKE :search OR author LIKE :search OR publisher LIKE :search)";
    $params['search'] = "%$search%";
}

// Count total books
$stmt = $pdo->prepare("SELECT COUNT(*) FROM book $where");
$stmt->execute($params);
$total_books = $stmt->fetchColumn();
$total_pages = ceil($total_books / $limit);

// Fetch the books
$sql = "SELECT * FROM book $where";
switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    case 'title_asc': $sql .= " ORDER BY title ASC"; break;
    case 'title_desc': $sql .= " ORDER BY title DESC"; break;
    default: $sql .= " ORDER BY id DESC";
}
$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- HELPER: GET SAFE IMAGE ---
function getBookImage($book) {
    global $defaultBookImage; // Use the SVG icon defined above

    if (!empty($book['images'])) {
        $images = explode(',', $book['images']);
        $path = "uploads/" . trim($images[0]);
        // Check if file physically exists on server
        if (file_exists($path)) {
            return htmlspecialchars($path);
        }
    }
    // If no image or file doesn't exist, return SVG icon
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
        
        <input type="text" name="search" placeholder="Search in <?= htmlspecialchars($subcategory) ?>..." value="<?= htmlspecialchars($search) ?>">
        
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
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <img src="<?= getBookImage($book) ?>" 
                             alt="<?= htmlspecialchars($book['title']) ?>"
                             onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                        </a>
                        
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">
                            Author: <?= htmlspecialchars($book['author']) ?>
                        </p>
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
                <p style="width:100%; text-align:center;">No books found in <?= ucfirst($subcategory) ?>.</p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?sub=<?= urlencode($subcategory) ?>&p=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" class="<?= $i==$page_no?'active':'' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
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