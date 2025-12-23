<?php
require_once 'db.php';

// --- 1. CONFIGURATION & VARIABLES ---
$errors = [];
$success = '';
$categories = [
    'Fiction' => ['Novel','Comic'],
    'Non-Fiction' => ['Biography','Self-help'],
    'Education' => ['Textbook'],
    'Children' => ['Color Book']
];

$title = $description = $author = $publisher = $cat_val = $sub_val = $language = $price = $stock = '';

// --- 2. FORM PROCESSING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $cat_val = $_POST['category'] ?? '';
    $sub_val = $_POST['subcategory'] ?? '';
    $language = $_POST['language'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    $coverName = '';
    $imageNames = [];
    $videoName = '';

    // A. Handle Cover (Required)
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $coverName = uniqid('cover_') . '.' . $ext;
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], "uploads/$coverName");
    } else {
        $errors[] = "Main Cover Image is required.";
    }

    // B. Handle Gallery (Backend check for 4 images)
    if (isset($_FILES['images']) && !empty(array_filter($_FILES['images']['tmp_name']))) {
        $filesArr = $_FILES['images'];
        $count = count($filesArr['name']);
        
        if ($count > 4) {
            $errors[] = "Maximum 4 gallery images allowed.";
        } else {
            for ($i = 0; $i < $count; $i++) {
                if ($filesArr['error'][$i] === UPLOAD_ERR_OK) {
                    $filename = uniqid('img_') . '.' . pathinfo($filesArr['name'][$i], PATHINFO_EXTENSION);
                    move_uploaded_file($filesArr['tmp_name'][$i], "uploads/$filename");
                    $imageNames[] = $filename;
                }
            }
        }
    }
    $imagesStr = implode(',', $imageNames);

    // C. Handle Optional Video
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $videoName = uniqid('vid_') . '.' . pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        if (!is_dir('uploads/videos')) mkdir('uploads/videos', 0777, true);
        move_uploaded_file($_FILES['video']['tmp_name'], "uploads/videos/$videoName");
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO book (title, description, author, publisher, category, subcategory, language, price, stock, cover_image, images, video) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $author, $publisher, $cat_val, $sub_val, $language, $price, $stock, $coverName, $imagesStr, $videoName]);
            $success = "Product added successfully!";
            // Reset fields
            $title = $description = $author = $publisher = $cat_val = $sub_val = $language = $price = $stock = '';
        } catch (PDOException $e) { $errors[] = "DB Error: " . $e->getMessage(); }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
    <style>
        .product-page { max-width: 1200px; margin: 20px auto; padding: 0 20px; font-family: 'Segoe UI', sans-serif; color: #374151; }
        .product-form { display: flex; gap: 20px; align-items: flex-start; }
        .product-left { flex: 1.5; display: flex; flex-direction: column; gap: 10px; }
        .product-right { flex: 1; display: flex; flex-direction: column; gap: 10px; position: sticky; top: 10px; }
        
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin-bottom: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; margin-bottom: 10px; font-size: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 5px; color: #2563eb; }
        
        .product-form label { display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px; margin-top: 8px; color: #4b5563; }
        .product-form input, .product-form select, .product-form textarea { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 0.9rem; }
        
        .success-box { background: #d1fae5; color: #065f46; padding: 12px; border: 1px solid #34d399; border-radius: 6px; margin-bottom: 15px; font-weight: 600; text-align: center; }
        .error-box { background: #fee2e2; color: #b91c1c; padding: 12px; border: 1px solid #f87171; border-radius: 6px; margin-bottom: 15px; font-weight: 600; }

        .btn-primary { background: #2563eb; color: white; border: none; padding: 12px; border-radius: 6px; width: 100%; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-primary:hover { background: #1d4ed8; }

        #cover-preview img { width: 110px; height: 150px; object-fit: contain; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9fafb; }
        #images-preview { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; }
        #images-preview img { width: 55px; height: 55px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
    </style>
</head>
<body>
<div class="product-page">
    <h2 style="margin-bottom: 15px;">Add New Product</h2>

    <?php if ($success): ?><div class="success-box"><?= $success ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="error-box"><?php foreach($errors as $e) echo $e."<br>"; ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="product-form">
        <div class="product-left">
            <div class="card">
                <h3>Basic Information</h3>
                <label>Book Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>
                <label>Book Description</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($description) ?></textarea>
            </div>
            <div class="card">
                <h3>Author & Details</h3>
                <label>Author</label>
                <input type="text" name="author" value="<?= htmlspecialchars($author) ?>">
                <label>Publisher</label>
                <input type="text" name="publisher" value="<?= htmlspecialchars($publisher) ?>">
            </div>
            <div class="card">
                <h3>Category Classification</h3>
                <label>Main Category</label>
                <select name="category" id="catSel" required>
                    <option value="">-- Select --</option>
                    <?php foreach($categories as $c => $s): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Subcategory</label>
                <select name="subcategory" id="subSel"></select>
            </div>
        </div>

        <div class="product-right">
            <div class="card">
                <h3>Pricing & Stock</h3>
                <label>Price (RM)</label>
                <input type="number" step="0.01" name="price" value="<?= $price ?>">
                <label>Stock Quantity</label>
                <input type="number" name="stock" value="<?= $stock ?>">
            </div>
            
            <div class="card">
                <h3>Main Cover</h3>
                <input type="file" name="cover_image" id="coverInput" accept="image/*">
                <div id="cover-preview"></div>
            </div>

            <div class="card">
                <h3>Image Gallery</h3>
                <input type="file" name="images[]" id="imagesInput" accept="image/*" multiple>
                <div id="images-preview"></div>
            </div>

            <div class="card">
                <h3>Product Video (Optional)</h3>
                <input type="file" name="video" accept="video/*">
            </div>

            <button type="submit" class="btn-primary">Save Product</button>
        </div>
    </form>
</div>

<script>
    // Dynamic Categories
    const cats = <?= json_encode($categories) ?>;
    const cS = document.getElementById('catSel');
    const sS = document.getElementById('subSel');
    cS.addEventListener('change', () => {
        sS.innerHTML = '<option value="">Select Sub</option>';
        if(cS.value) cats[cS.value].forEach(v => {
            const o = document.createElement('option'); o.value = o.text = v; sS.appendChild(o);
        });
    });

    // Cover Preview
    document.getElementById('coverInput').addEventListener('change', function() {
        const preview = document.getElementById('cover-preview');
        preview.innerHTML = '';
        if(this.files[0]) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(this.files[0]);
            preview.appendChild(img);
        }
    });

    // Gallery Alert (Max 4) & Preview
    document.getElementById('imagesInput').addEventListener('change', function() {
        const preview = document.getElementById('images-preview');
        preview.innerHTML = '';
        if (this.files.length > 4) {
            alert("Error: You are only allowed to upload a maximum of 4 gallery images.");
            this.value = ""; 
            return;
        }
        Array.from(this.files).forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            preview.appendChild(img);
        });
    });
</script>
</body>
</html>