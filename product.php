<?php
// --- 1. DATABASE & CONFIGURATION ---
// Connect to the database and set the default image path
require_once 'db.php';
$defaultBookImage = "uploads/download.svg";

// Check if a book ID was sent in the URL (e.g., product.php?id=12)
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// Convert ID to a number and fetch the specific book from the database
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM book WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

// If no book matches that ID, stop the script
if (!$book) die("Book not found.");

// --- 2. MEDIA GALLERY LOGIC ---
// Convert the comma-separated image string into a list (array)
$images = !empty($book['images']) ? explode(',', $book['images']) : [];
// Check if a video file name exists for this book
$videoFile = !empty($book['video']) ? $book['video'] : null;

// Decide what the "Main Display" should show when the page first loads
// Priority: 1. Cover Image, 2. First Gallery Image, 3. Default SVG Icon
if (!empty($book['cover_image']) && file_exists("uploads/" . $book['cover_image'])) {
    $mainImage = "uploads/" . $book['cover_image'];
} else {
    $mainImage = !empty($images) ? "uploads/" . trim($images[0]) : $defaultBookImage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Layout for the book details grid */
        .book-container { display: grid; grid-template-columns: 12fr 8fr; gap: 40px; max-width: 1100px; margin: 0 auto; padding: 20px; }
        .gallery-wrapper { display: flex; gap: 15px; }
        .thumbnail-strip { display: flex; flex-direction: column; gap: 10px; }
        
        /* Thumbnail styles */
        .thumb { width: 60px; height: 80px; object-fit: cover; cursor: pointer; border: 1px solid #ddd; opacity: 0.7; transition: 0.3s; position: relative; overflow: hidden; }
        .thumb.active, .thumb:hover { opacity: 1; border-color: #333; }
        
        /* Video thumbnail special overlay */
        .video-thumb-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); z-index: 2; }
        
        /* Main image and video viewing area */
        .main-image-area { background-color: #f9f9f9; width: 100%; height: 500px; display: flex; justify-content: center; align-items: center; overflow: hidden; position: relative; border-radius: 8px; }
        .main-image-area img, .main-image-area video { max-width: 100%; max-height: 100%; object-fit: contain; }
        
        /* Typography and product info styles */
        .product-info h1 { font-size: 2rem; margin-bottom: 10px; }
        .price-range { color: #5bc0de; font-size: 1.5rem; margin-bottom: 20px; font-weight: bold; }
        .specs { margin-bottom: 20px; color: #666; font-size: 0.9rem; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .description-list { padding-left: 20px; margin-bottom: 30px; line-height: 1.6; color: #555; }
        .shipping-note li { color: #d9534f; font-size: 0.9rem; margin-bottom: 8px; border-left: 2px solid #d9534f; padding-left: 10px; list-style: none; }
        
        /* Add to cart section */
        .action-box { background: #f9f9f9; padding: 20px; border-top: 1px solid #eee; }
        .add-btn { background-color: #304458ff; color: white; width: 100%; padding: 15px; border: none; cursor: pointer; text-transform: uppercase; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="book-container">
        <div class="gallery-wrapper">
            <div class="thumbnail-strip">
               <?php if ($videoFile): ?>
                    <div class="thumb video-thumb" onclick="showVideo(this)">
                        <div class="video-thumb-overlay">
                            <div class="play-triangle"></div> 
                        </div>
                        <video style="width: 100%; height: 100%; object-fit: cover;">
                            <source src="uploads/videos/<?= htmlspecialchars($videoFile) ?>#t=0.5" type="video/mp4">
                        </video>
                    </div>
                <?php endif; ?>

                <?php foreach($images as $img): ?>
                    <img src="uploads/<?= trim($img) ?>" 
                         class="thumb" 
                         onclick="showImage(this)" 
                         onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                <?php endforeach; ?>
            </div>

            <div class="main-image-area" id="mainDisplayArea">
                <img id="mainImg" src="<?= $mainImage ?>" alt="<?= htmlspecialchars($book['title']) ?>" onerror="this.onerror=null; this.src='<?= $defaultBookImage ?>';">
                
                <?php if ($videoFile): ?>
                    <video id="mainVideo" controls style="display: none; width: 100%; height: 100%; background: #000;">
                        <source src="uploads/videos/<?= htmlspecialchars($videoFile) ?>" type="video/mp4">
                    </video>
                <?php endif; ?>
            </div>
        </div>

        <div class="product-info">
            <h1><?= htmlspecialchars($book['title']) ?></h1>
            <div class="price-range">RM<?= number_format($book['price'], 2) ?></div>
            
            <div class="specs">
                Author: <?= htmlspecialchars($book['author']) ?> | 
                Publisher: <?= htmlspecialchars($book['publisher']) ?>
            </div>
            
            <div class="description-list"><?= nl2br(htmlspecialchars($book['description'])) ?></div>
            
            <div class="shipping-note">
                <ul>
                    <li><strong>Stock:</strong> <?= $book['stock'] ?> units available.</li>
                    <li>Images are for illustration purposes only.</li>
                </ul>
            </div>

            <form action="add_to_cart.php" method="POST" class="action-box">
                <input type="number" name="quantity" value="1" min="1" max="<?= $book['stock'] ?>" style="width: 60px; margin-bottom: 10px;">
                <button type="submit" class="add-btn">Add to cart</button>
            </form>
        </div>
    </div>

    <div id="footer-placeholder"></div>

    <script>
        // --- 7. JAVASCRIPT FOR MEDIA SWITCHING ---
        const mainImg = document.getElementById('mainImg');
        const mainVideo = document.getElementById('mainVideo');

        // Function to switch display to an Image
        function showImage(element) {
            mainImg.style.display = 'block'; // Show image
            if(mainVideo) {
                mainVideo.style.display = 'none'; // Hide video
                mainVideo.pause(); // Stop video audio
            }
            
            mainImg.src = element.src; // Set main display to thumbnail's source
            
            // Highlight the active thumbnail
            document.querySelectorAll('.thumb').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
        }

        // Function to switch display to the Video
        function showVideo(element) {
            mainImg.style.display = 'none'; // Hide image
            if(mainVideo) {
                mainVideo.style.display = 'block'; // Show video
                mainVideo.play(); // Start playback automatically
            }

            // Highlight the active thumbnail
            document.querySelectorAll('.thumb').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
        }

        // --- 8. FOOTER FETCH ---
        // Dynamically load the footer HTML file
        fetch('footer.html')
        .then(r => r.text())
        .then(data => { document.getElementById('footer-placeholder').innerHTML = data; });
    </script>
</body>
</html>