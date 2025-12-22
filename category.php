<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
    $_user = $_SESSION['user']; 
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $_user = ['username' => 'Member']; 
}

$subcategory = $_GET['sub'] ?? 'novel';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id_desc';

$where = "WHERE subcategory = :subcategory";
$params = ['subcategory' => $subcategory];

if ($search) {
    $where .= " AND (title LIKE :search OR author LIKE :search)";
    $params['search'] = "%$search%";
}

$sql = "SELECT * FROM book $where";
switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    default: $sql .= " ORDER BY id DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Category: <?= ucfirst(htmlspecialchars($subcategory)) ?></title>
    <link rel="stylesheet" href="style.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="product-section" style="max-width: 1200px; margin: 40px auto; padding: 20px; min-height: 70vh;">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 class="section-title">Category: <?= ucfirst(htmlspecialchars($subcategory)) ?></h2>
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="sub" value="<?= htmlspecialchars($subcategory) ?>">
                <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="submit" style="padding: 8px 15px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer;">Search</button>
            </form>
        </div>

        <div class="product-container">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <?php 
                                $imgs = explode(',', $book['images']);
                                $imgSrc = !empty($imgs[0]) ? "uploads/".trim($imgs[0]) : "includes/default-book.png";
                            ?>
                            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                        </a>
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="price">$<?= number_format($book['price'], 2) ?></p>
                        <p style="font-size: 0.9rem; color: #666;">Stock: <?= $book['stock'] ?></p>
                        <div style="margin-top: 15px;">
                            <a href="add_to_cart.php?id=<?= $book['id'] ?>" class="add-to-cart-btn" style="display: block; background: #2c3e50; color: white; padding: 10px; text-decoration: none; border-radius: 4px; font-weight: bold;">Add to Cart</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No books found in this category.</p>
            <?php endif; ?>
        </div>
    </main>

    <div id="footer-placeholder"></div>
    <script>
        fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
        $(document).ready(function() { $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); }); });
    </script>
</body>
</html>