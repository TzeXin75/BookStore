<?php
require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM book WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) { die("Book not found."); }

$images = !empty($book['images']) ? explode(',', $book['images']) : ['default.png'];
$mainImage = "uploads/" . trim($images[0]);

include 'includes/header.php';
?>

<div class="book-container" style="max-width: 1100px; margin: 40px auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; padding: 20px;">
    <div class="gallery-section">
        <img id="mainImg" src="<?= $mainImage ?>" style="width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <?php foreach($images as $img): ?>
                <img src="uploads/<?= trim($img) ?>" class="thumb" style="width: 60px; height: 80px; cursor: pointer; border: 1px solid #ddd;" onclick="document.getElementById('mainImg').src=this.src">
            <?php endforeach; ?>
        </div>
    </div>

    <div class="info-section">
        <h1 style="margin-bottom: 10px;"><?= htmlspecialchars($book['title']) ?></h1>
        <p style="color: #28a745; font-size: 1.8rem; font-weight: bold; margin-bottom: 20px;">$<?= number_format($book['price'], 2) ?></p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
            <p><strong>Publisher:</strong> <?= htmlspecialchars($book['publisher']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($book['category']) ?></p>
            <p style="margin-top: 15px; color: <?= ($book['stock'] > 0) ? '#28a745' : '#dc3545' ?>;">
                <strong>Availability:</strong> <?= ($book['stock'] > 0) ? $book['stock'] . ' units in stock' : 'Out of Stock' ?>
            </p>
        </div>

        <div style="margin-top: 30px;">
            <?php if ($book['stock'] > 0): ?>
                <a href="add_to_cart.php?id=<?= $book['id'] ?>" class="btn-primary" style="display: block; text-align: center; padding: 15px; background: #2c3e50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase;">Add to Cart</a>
            <?php else: ?>
                <button disabled style="width: 100%; padding: 15px; background: #ccc; border: none; color: #666; border-radius: 5px;">Out of Stock</button>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; line-height: 1.6; color: #555;">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>