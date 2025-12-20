<?php
require_once 'config/db_connect.php';

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

include 'includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>Category: <?= ucfirst(htmlspecialchars($subcategory)) ?></h2>
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="hidden" name="sub" value="<?= htmlspecialchars($subcategory) ?>">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px;">
            <button type="submit" style="padding: 8px 15px;">Search</button>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
        <?php foreach ($books as $book): ?>
            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; background: white;">
                <a href="product.php?id=<?= $book['id'] ?>">
                    <?php 
                        $imgs = explode(',', $book['images']);
                        $imgSrc = !empty($imgs[0]) ? "uploads/".trim($imgs[0]) : "includes/default-book.png";
                    ?>
                    <img src="<?= $imgSrc ?>" style="width: 150px; height: 200px; object-fit: cover; margin-bottom: 15px;">
                </a>
                <h3 style="font-size: 1.1rem; height: 50px; overflow: hidden;"><?= htmlspecialchars($book['title']) ?></h3>
                <p style="color: #28a745; font-weight: bold; font-size: 1.2rem;">$<?= number_format($book['price'], 2) ?></p>
                
                <p style="font-size: 0.85rem; color: <?= ($book['stock'] > 0) ? '#666' : 'red' ?>;">
                    <?= ($book['stock'] > 0) ? 'Stock: ' . $book['stock'] : 'Out of Stock' ?>
                </p>

                <div style="margin-top: 15px;">
                    <?php if ($book['stock'] > 0): ?>
                        <a href="add_to_cart.php?id=<?= $book['id'] ?>" style="display: block; background: #2c3e50; color: white; padding: 10px; text-decoration: none; border-radius: 4px; font-size: 0.9rem;">Add to Cart</a>
                    <?php else: ?>
                        <span style="display: block; background: #eee; color: #999; padding: 10px; border-radius: 4px; font-size: 0.9rem;">Unavailable</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>