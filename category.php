<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

// BRIDGE: Ensure header.php recognizes the user so buttons appear
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
    <style>
        /* Fallback for icons since CDN is removed */
        .fa-search::before { content: "🔍"; font-style: normal; }
        .fa-shopping-cart::before { content: "Cart"; font-family: sans-serif; font-size: 0.8em; }
        .fa-history::before { content: "Orders"; font-family: sans-serif; font-size: 0.8em; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 40px auto; padding: 20px; min-height: 70vh;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2>Category: <?= ucfirst(htmlspecialchars($subcategory)) ?></h2>
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="sub" value="<?= htmlspecialchars($subcategory) ?>">
                <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="submit" style="padding: 8px 15px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer;">Search</button>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <a href="product.php?id=<?= $book['id'] ?>">
                            <?php 
                                $imgs = explode(',', $book['images']);
                                $imgSrc = !empty($imgs[0]) ? "uploads/".trim($imgs[0]) : "includes/default-book.png";
                            ?>
                            <img src="<?= $imgSrc ?>" style="width: 150px; height: 210px; object-fit: cover; margin-bottom: 15px;">
                        </a>
                        <h3 style="font-size: 1.1rem; height: 50px; overflow: hidden;"><?= htmlspecialchars($book['title']) ?></h3>
                        <p style="color: #28a745; font-weight: bold; font-size: 1.2rem;">$<?= number_format($book['price'], 2) ?></p>
                        <p style="font-size: 0.85rem;">Stock: <?= $book['stock'] ?></p>
                        <div style="margin-top: 15px;">
                            <a href="add_to_cart.php?id=<?= $book['id'] ?>" style="display: block; background: #2c3e50; color: white; padding: 10px; text-decoration: none; border-radius: 4px; font-weight: bold;">Add to Cart</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No books found in this category.</p>
            <?php endif; ?>
        </div>
    </main>

    <div id="footer-placeholder"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
        $(document).ready(function() { $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); }); });
    </script>
</body>
</html>