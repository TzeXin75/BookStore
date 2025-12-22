<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM book WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) { die("Book not found."); }

$images = !empty($book['images']) ? explode(',', $book['images']) : ['default.png'];
$mainImage = "uploads/" . trim($images[0]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($book['title']) ?> - Bookstore</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main style="max-width: 1100px; margin: 40px auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; padding: 20px; min-height: 60vh;">
        <div class="gallery-section">
            <img id="mainImg" src="<?= $mainImage ?>" style="width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <?php foreach($images as $img): ?>
                    <img src="uploads/<?= trim($img) ?>" style="width: 60px; height: 80px; cursor: pointer; border: 1px solid #ddd;" onclick="document.getElementById('mainImg').src=this.src">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-section">
            <h1><?= htmlspecialchars($book['title']) ?></h1>
            <p style="color: #28a745; font-size: 1.8rem; font-weight: bold; margin-bottom: 20px;">$<?= number_format($book['price'], 2) ?></p>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                <p><strong>Publisher:</strong> <?= htmlspecialchars($book['publisher']) ?></p>
                <p><strong>Category:</strong> <?= htmlspecialchars($book['category']) ?></p>
                <!-- Restored Stock Availability -->
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
                        <button type="submit" style="width: 100%; border: none; padding: 15px; background: #2c3e50; color: white; border-radius: 5px; font-weight: bold; cursor: pointer;">Add to Cart</button>
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
    </main>

    <div id="footer-placeholder"></div>
    <script>
    function changeQty(amt) { const i = document.getElementById('book_qty'); applyBounds(parseInt(i.value) + amt); }
    function validateQty() { applyBounds(parseInt(document.getElementById('book_qty').value)); }
    function applyBounds(v) { const i = document.getElementById('book_qty'); const m = parseInt(i.max); if (v < 1 || isNaN(v)) v = 1; if (v > m) v = m; i.value = v; }
    fetch('footer.html').then(r => r.text()).then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    $(document).ready(function() { $('#hamburger').click(function() { $('#navLinks').toggleClass('active'); }); });
    </script>
</body>
</html>