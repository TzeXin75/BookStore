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

<style>

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

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
                <form action="add_to_cart.php" method="POST">
                    <input type="hidden" name="id" value="<?= $book['id'] ?>">
                    
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                        <span style="font-weight: bold;">Quantity:</span>
                        <div style="display: flex; border: 1px solid #ccc; border-radius: 5px; overflow: hidden; width: 120px;">
                            <button type="button" onclick="changeQty(-1)" style="flex: 1; padding: 10px; background: #eee; border: none; cursor: pointer;">-</button>
                            <input type="number" name="quantity" id="book_qty" value="1" min="1" max="<?= $book['stock'] ?>" style="width: 40px; text-align: center; border: none; font-weight: bold;" onchange="validateQty()">
                            <button type="button" onclick="changeQty(1)" style="flex: 1; padding: 10px; background: #eee; border: none; cursor: pointer;">+</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; border: none; padding: 15px; background: #2c3e50; color: white; border-radius: 5px; font-weight: bold; text-transform: uppercase; cursor: pointer;">
                        Add to Cart
                    </button>
                </form>
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

<script>
function changeQty(amt) {
    const input = document.getElementById('book_qty');
    let current = parseInt(input.value) || 1;
    applyBounds(current + amt);
}

function validateQty() {
    const input = document.getElementById('book_qty');
    applyBounds(parseInt(input.value));
}

function applyBounds(val) {
    const input = document.getElementById('book_qty');
    const max = parseInt(input.max);
    if (val < 1 || isNaN(val)) val = 1;
    if (val > max) val = max;
    input.value = val;
}
</script>

<?php include 'includes/footer.php'; ?>