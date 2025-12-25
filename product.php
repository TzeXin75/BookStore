<?php
$defaultBookImage = "uploads/download.svg"; 

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM book WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) { die("Book not found."); }

// --- MEDIA LOGIC ---
$images = !empty($book['images']) ? explode(',', $book['images']) : [];
$video = !empty($book['video']) ? $book['video'] : null;
$cover = !empty($book['cover_image']) ? "uploads/" . $book['cover_image'] : $defaultBookImage;

// 1. add all gallery images to the list
$galleryItems = [];
foreach ($images as $img) {
    $galleryItems[] = [
        'type' => 'image', 
        'src' => "uploads/" . trim($img)
    ];
}

// 2. append the video to the END of the sequence if it exists
if ($video) {
    $videoPath = "uploads/" . $video;
    
    // Check if the file actually exists on the server
    if (file_exists($videoPath)) {
        $videoItem = [
            'type' => 'video', 
            'src' => $videoPath, 
            'thumb' => $cover // Uses book cover as the video preview
        ];
        
        // Push to the end of the array
        $galleryItems[] = $videoItem;
    }
}

// 3. Determine the initial media to display in the main box
// Since the video is at the end, index 0 will be the first image
$initialMedia = ['type' => 'image', 'src' => $cover]; // Default fallback
if (!empty($galleryItems)) {
    $initialMedia = $galleryItems[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($book['title']) ?> - SIX SEVEN BS</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        
        /* Gallery Styles */
        .gallery-container { position: relative; width: 100%; height: 500px; background: #f9f9f9; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        #mainImg, #mainVid { width: 100%; height: 100%; object-fit: contain; }
        
        #mainVid { display: <?= $initialMedia['type'] === 'video' ? 'block' : 'none' ?>; background: #000; }
        #mainImg { display: <?= $initialMedia['type'] === 'image' ? 'block' : 'none' ?>; }
        
        /* Thumbnail Styles */
        .thumb-item { width: 60px; height: 80px; cursor: pointer; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #eee; display: flex; align-items: center; justify-content: center; transition: 0.2s; position: relative; }
        .thumb-item:hover { border-color: #2c3e50; opacity: 0.8; }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

        /* Video Overlay for Thumbnail */
        .video-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4); /* Darkens the cover slightly */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Pure CSS Play Triangle */
        .play-triangle {
            width: 0; height: 0; 
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-left: 12px solid white;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main style="max-width: 1100px; margin: 40px auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; padding: 20px; min-height: 60vh;">
        
        <div class="gallery-section">
            <div class="gallery-container">
                <img id="mainImg" src="<?= $initialMedia['type'] === 'image' ? $initialMedia['src'] : '' ?>">
                <video id="mainVid" controls>
                    <source id="vidSource" src="<?= $initialMedia['type'] === 'video' ? $initialMedia['src'] : '' ?>" type="video/mp4">
                </video>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
                <?php foreach($galleryItems as $item): ?>
                    <?php if ($item['type'] === 'image'): ?>
                        <div class="thumb-item" onclick="showMedia('image', '<?= $item['src'] ?>')">
                            <img src="<?= $item['src'] ?>">
                        </div>
                    <?php else: ?>
                        <div class="thumb-item" onclick="showMedia('video', '<?= $item['src'] ?>')">
                            <img src="<?= $item['thumb'] ?>">
                            <div class="video-overlay">
                                <div class="play-triangle"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-section">
            <h1><?= htmlspecialchars($book['title']) ?></h1>
            <p style="color: #28a745; font-size: 1.8rem; font-weight: bold; margin-bottom: 20px;">RM <?= number_format($book['price'], 2) ?></p>
            
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
                        <button type="submit" style="width: 100%; border: none; padding: 15px; background: #2c3e50; color: white; border-radius: 5px; font-weight: bold; cursor: pointer;">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <button disabled style="width: 100%; padding: 15px; background: #ccc; border: none; color: #666; border-radius: 5px;">Out of Stock</button>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; line-height: 1.6; color: #555;">
                <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px;">Description</h3>
                <div id="descContainer" class="description-wrapper collapsed">
                    <p id="descText"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                </div>
                <button id="readMoreBtn" onclick="toggleDescription()">Read More</button>
            </div>
        </div>
    </main>

    <div id="footer-placeholder"></div>
    
    <script>
        function showMedia(type, src) {
            const imgEl = document.getElementById('mainImg');
            const vidEl = document.getElementById('mainVid');
            const vidSource = document.getElementById('vidSource');

            if (type === 'image') {
                vidEl.pause();
                vidEl.style.display = 'none';
                imgEl.style.display = 'block';
                imgEl.src = src;
            } else {
                imgEl.style.display = 'none';
                vidEl.style.display = 'block';
                vidSource.src = src;
                vidEl.load();
                vidEl.play();
            }
        }

        function changeQty(amt) { 
            const i = document.getElementById('book_qty'); 
            applyBounds(parseInt(i.value) + amt); 
        }
        function validateQty() { 
            applyBounds(parseInt(document.getElementById('book_qty').value)); 
        }
        function applyBounds(v) { 
            const i = document.getElementById('book_qty'); 
            const m = parseInt(i.max); 
            if (v < 1 || isNaN(v)) v = 1; 
            if (v > m) v = m; 
            i.value = v; 
        }
        
        fetch('footer.html').then(r => r.text()).then(data => { 
            document.getElementById('footer-placeholder').innerHTML = data; 
        });

        document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById('descContainer');
    const text = document.getElementById('descText');
    const btn = document.getElementById('readMoreBtn');

    // Check if the actual text height is greater than the collapsed container height
    if (text.offsetHeight > 200) {
        btn.style.display = 'block';
    } else {
        container.classList.remove('collapsed');
    }
});

function toggleDescription() {
    const container = document.getElementById('descContainer');
    const btn = document.getElementById('readMoreBtn');

    if (container.classList.contains('collapsed')) {
        container.classList.remove('collapsed');
        container.classList.add('expanded');
        btn.innerText = 'Show Less';
    } else {
        container.classList.add('collapsed');
        container.classList.remove('expanded');
        btn.innerText = 'Read More';
        // Smooth scroll back to description title if desired
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}
    </script>
</body>
</html>