<?php
require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (isset($_db) && !isset($pdo)) { $pdo = $_db; }

$categories = [
    'Fiction' => ['Novel', 'Comic'],
    'Non-Fiction' => ['Biography', 'Self-help'],
    'Education' => ['Textbook'],
    'Children' => ['Color Book']
];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM book WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("<div style='padding:20px; color:red;'>Error: Product not found.</div>");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $publisher   = trim($_POST['publisher'] ?? '');
    $category    = $_POST['category'] ?? '';
    $subcategory = $_POST['subcategory'] ?? '';
    $language    = $_POST['language'] ?? '';
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);

    $cover_image = $product['cover_image']; 
    $images_csv  = $product['images'];
    $video_file  = $product['video'];

    // --- COVER IMAGE HANDLING ---
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $new_cover_name = 'cover_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], "uploads/$new_cover_name")) {
            if (!empty($product['cover_image']) && file_exists("uploads/" . $product['cover_image'])) {
                unlink("uploads/" . $product['cover_image']);
            }
            $cover_image = $new_cover_name;
        }
    }

    // --- GALLERY IMAGES HANDLING (MAX 4) ---
    if (!empty($_FILES['gallery']['name'][0])) {
        $valid_uploads = array_filter($_FILES['gallery']['error'], fn($e) => $e === UPLOAD_ERR_OK);
        if (count($valid_uploads) > 4) {
            $errors[] = "You can only upload a maximum of 4 gallery images.";
        } else {
            $new_gallery_files = [];
            foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_path) {
                if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['gallery']['name'][$key], PATHINFO_EXTENSION);
                    $new_name = 'gal_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmp_path, "uploads/$new_name")) {
                        $new_gallery_files[] = $new_name;
                    }
                }
            }
            if (!empty($new_gallery_files)) {
                if (!empty($product['images'])) {
                    $old_images = explode(',', $product['images']);
                    foreach ($old_images as $old_img) {
                        $p = "uploads/" . trim($old_img);
                        if (file_exists($p)) unlink($p);
                    }
                }
                $images_csv = implode(',', $new_gallery_files);
            }
        }
    }

    // --- VIDEO HANDLING ---
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $v_ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        if (in_array($v_ext, ['mp4', 'webm'])) {
            $new_video_name = 'vid_' . uniqid() . '.' . $v_ext;
            if (move_uploaded_file($_FILES['video']['tmp_name'], "uploads/$new_video_name")) {
                if (!empty($product['video']) && file_exists("uploads/" . $product['video'])) {
                    unlink("uploads/" . $product['video']);
                }
                $video_file = $new_video_name;
            }
        } else {
            $errors[] = "Invalid video format. Only MP4 and WebM are allowed.";
        }
    }

    if (empty($errors)) {
        $update_sql = "UPDATE book SET 
            title=?, description=?, author=?, publisher=?, 
            category=?, subcategory=?, language=?, price=?, 
            stock=?, cover_image=?, images=?, video=? 
            WHERE id=?";
        
        $params = [$title, $description, $author, $publisher, 
                   $category, $subcategory, $language, $price, 
                   $stock, $cover_image, $images_csv, $video_file, $id];
        
        if ($pdo->prepare($update_sql)->execute($params)) {
            $success = "✅ Product updated successfully!";
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>

<div class="product-page" style="padding: 20px; font-family: 'Segoe UI', sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Edit Book: <?= htmlspecialchars($product['title']) ?></h2>
        <a href="admin.php?page=product_dir" style="color: #2563eb; text-decoration: none; font-weight: 600;">&larr; Back to Directory</a>
    </div>

    <?php if ($success): ?><div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;"><?= $success ?></div><?php endif; ?>
    <?php if ($errors): ?><div style="background: #fef2f2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fee2e2;"><?php foreach ($errors as $err): ?><p style="margin:0;"><?= $err ?></p><?php endforeach; ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card" style="background: #fff; padding: 25px; border: 1px solid #e5e7eb; border-radius: 10px;">
                <h3 style="margin-top: 0; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">Book Information</h3>
                
                <label style="display:block; margin: 15px 0 5px; font-weight: 600;">Book Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>

                <div style="display: flex; gap: 15px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label style="display:block; margin-bottom: 5px; font-weight: 600;">Author</label>
                        <input type="text" name="author" value="<?= htmlspecialchars($product['author']) ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display:block; margin-bottom: 5px; font-weight: 600;">Publisher</label>
                        <input type="text" name="publisher" value="<?= htmlspecialchars($product['publisher']) ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>

                <label style="display:block; margin: 15px 0 5px; font-weight: 600;">Description</label>
                <textarea name="description" rows="6" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="card" style="background: #fff; padding: 25px; border: 1px solid #e5e7eb; border-radius: 10px;">
                <h3 style="margin-top: 0; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">Gallery & Media</h3>
                
                <label style="display:block; margin: 15px 0 10px; font-weight: 600;">Gallery Images (Current Max 4):</label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                    <?php if(!empty($product['images'])): 
                        foreach(explode(',', $product['images']) as $img): ?>
                        <img src="uploads/<?= trim($img) ?>" style="width: 80px; height: 110px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                    <?php endforeach; endif; ?>
                </div>
                <input type="file" name="gallery[]" multiple accept="image/*">

                <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

                <label style="display:block; margin-bottom: 10px; font-weight: 600;">Product Video:</label>
                <?php if(!empty($product['video'])): ?>
                    <video width="100%" controls style="max-width: 300px; border-radius: 8px; background: #000; margin-bottom: 10px;"><source src="uploads/<?= $product['video'] ?>" type="video/mp4"></video>
                <?php endif; ?>
                <input type="file" name="video" accept="video/mp4,video/webm">
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card" style="background: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 10px; text-align: center;">
                <h3 style="margin-top: 0; font-size: 1rem;">Primary Cover</h3>
                <img src="uploads/<?= $product['cover_image'] ?>" style="width: 140px; height: 190px; object-fit: cover; border-radius: 5px; border: 1px solid #eee; margin-bottom: 15px;">
                <input type="file" name="cover_image" accept="image/*" style="font-size: 0.8rem; width: 100%;">
            </div>

            <div class="card" style="background: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 10px;">
                <h3 style="margin-top: 0; font-size: 1rem;">Inventory & Specs</h3>
                
                <label style="display:block; margin-bottom: 5px; font-size: 0.9rem;">Price (RM)</label>
                <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px;">

                <label style="display:block; margin-bottom: 5px; font-size: 0.9rem;">Stock Level</label>
                <input type="number" name="stock" value="<?= $product['stock'] ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px;">

                <label style="display:block; margin-bottom: 5px; font-size: 0.9rem;">Language</label>
                <select name="language" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px;">
                    <?php foreach (['English', 'Chinese', 'Malay', 'Tamil'] as $lang): ?>
                        <option value="<?= $lang ?>" <?= $product['language'] == $lang ? 'selected' : '' ?>><?= $lang ?></option>
                    <?php endforeach; ?>
                </select>

                <label style="display:block; margin-bottom: 5px; font-size: 0.9rem;">Category</label>
                <select name="category" id="categorySelect" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px;">
                    <?php foreach ($categories as $cat => $subs): ?>
                        <option value="<?= $cat ?>" <?= $product['category'] == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>

                <label style="display:block; margin-bottom: 5px; font-size: 0.9rem;">Subcategory</label>
                <select name="subcategory" id="subcategorySelect" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;"></select>

                <button type="submit" style="width: 100%; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Update Product</button>
            </div>
        </div>
    </form>
</div>

<script>
const categoryMap = <?= json_encode($categories) ?>;
const catEl = document.getElementById('categorySelect');
const subEl = document.getElementById('subcategorySelect');

function updateSubs(selected = null) {
    const val = catEl.value;
    subEl.innerHTML = '';
    if (categoryMap[val]) {
        categoryMap[val].forEach(s => {
            const opt = document.createElement('option');
            opt.value = s;
            opt.text = s;
            if (selected && s === selected) opt.selected = true;
            subEl.appendChild(opt);
        });
    }
}
catEl.addEventListener('change', () => updateSubs());
updateSubs("<?= $product['subcategory'] ?>");
</script>