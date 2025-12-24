<?php
//  $pdo is inherited from admin.php
$errors = [];
$success = '';
$categories = [
    'Fiction' => ['Novel','Comic'],
    'Non-Fiction' => ['Biography','Self-help'],
    'Education' => ['Textbook'],
    'Children' => ['Color Book']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $cat_val = $_POST['category'] ?? '';
    $sub_val = $_POST['subcategory'] ?? '';
    $language = $_POST['language'] ?? ''; 
    $price = floatval($_POST['price'] ?? 0);
    // Change currency from $ to RM
    $price = str_replace('$', 'RM', $price);
    $stock = intval($_POST['stock'] ?? 0);

    $coverName = ''; $imageNames = []; $videoName = '';

    // A. Handle Cover (Required)
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $coverName = uniqid('cover_') . '.' . pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], "uploads/$coverName");
    } else { $errors[] = "Main Cover is required."; }

    // B. Handle Gallery (Max 4)
    if (isset($_FILES['images']) && count(array_filter($_FILES['images']['tmp_name'])) > 4) {
        $errors[] = "Max 4 gallery images allowed.";
    } else {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fn = uniqid('img_') . '.' . pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                move_uploaded_file($tmp, "uploads/$fn");
                $imageNames[] = $fn;
            }
        }
    }
    
    // C. Handle Video
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $videoName = uniqid('vid_') . '.' . pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        if (!is_dir('uploads/')) mkdir('uploads/', 0777, true);
        move_uploaded_file($_FILES['video']['tmp_name'], "uploads/$videoName");
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO book (title, description, author, publisher, category, subcategory, language, price, stock, cover_image, images, video) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$title, $description, $author, $publisher, $cat_val, $sub_val, $language, $price, $stock, $coverName, implode(',', $imageNames), $videoName]);
            $success = "Product added successfully!";
        } catch (PDOException $e) { $errors[] = "DB Error: " . $e->getMessage(); }
    }
}
?>

<style>
    /* DESIGN MATCHING PRODUCT_DIR */
    .product-form { display: flex; gap: 20px; align-items: flex-start; }
    .product-left { flex: 1.5; display: flex; flex-direction: column; gap: 15px; }
    .product-right { flex: 1; display: flex; flex-direction: column; gap: 15px; position: sticky; top: 10px; }
    .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .card h3 { margin-top: 0; margin-bottom: 10px; font-size: 1rem; border-bottom: 2px solid #f3f4f6; padding-bottom: 5px; color: #2563eb; }
    .product-form label { display: block; font-weight: 600; font-size: 0.85rem; margin-top: 8px; }
    .product-form input, .product-form select, .product-form textarea { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
    .btn-primary { background: #2563eb; color: white; border: none; padding: 12px; border-radius: 6px; width: 100%; font-weight: bold; cursor: pointer; }

    /* PREVIEW STYLING */
    .preview-container { margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap; }
    .preview-container img { width: 80px; height: 110px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px; }
    .video-preview { width: 100%; margin-top: 10px; border-radius: 4px; display: none; }
</style>

<div class="product-page">
    <h2>Add New Product</h2>
    <?php if ($success): ?><div style="color:green; padding:10px;"><?= $success ?></div><?php endif; ?>
    <?php if ($errors): ?><div style="color:red; padding:10px;"><?php foreach($errors as $e) echo $e."<br>"; ?></div><?php endif; ?>

    <form method="POST" action="admin.php?page=add_product" enctype="multipart/form-data" class="product-form">
        <div class="product-left">
            <div class="card">
                <h3>Basic Information</h3>
                <label>Book Title</label>
                <input type="text" name="title" required>
                <label>Description</label>
                <textarea name="description" rows="5"></textarea>
            </div>
            <div class="card">
                <h3>Author & Publisher</h3>
                <label>Author</label>
                <input type="text" name="author">
                <label>Publisher</label>
                <input type="text" name="publisher">
            </div>
            <div class="card">
                <h3>Category</h3>
                <select name="category" id="catSel" required>
                    <option value="">-- Select --</option>
                    <?php foreach($categories as $c => $s): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?>
                </select>
                <label>Subcategory</label>
                <select name="subcategory" id="subSel" required></select>
            </div>
        </div>

        <div class="product-right">
            <div class="card">
                <h3>Pricing & Stock</h3>
                <label>Language</label>
                <select name="language" required>
                    <option value="English">English</option>
                    <option value="Chinese">Chinese</option>
                    <option value="Malay">Malay</option>
                </select>
                <label>Price (RM)</label>
                <input type="number" step="0.01" name="price">
                <label>Stock</label>
                <input type="number" name="stock">
            </div>

            <div class="card">
                <h3>Media Upload</h3>
                <label>Main Cover</label>
                <input type="file" name="cover_image" id="coverInput" accept="image/*" required>
                <div id="coverPreview" class="preview-container"></div>

                <label>Gallery (Max 4)</label>
                <input type="file" name="images[]" id="galleryInput" accept="image/*" multiple>
                <div id="galleryPreview" class="preview-container"></div>

                <label>Product Video</label>
                <input type="file" name="video" id="videoInput" accept="video/*">
                <video id="videoPreview" class="video-preview" controls></video>
            </div>

            <button type="submit" class="btn-primary">Save Product</button>
        </div>
    </form>
</div>



<script>
    // 1. Dynamic Subcategory Logic
    const catsData = <?= json_encode($categories) ?>;
    document.getElementById('catSel').addEventListener('change', function() {
        const sub = document.getElementById('subSel');
        sub.innerHTML = '';
        if(this.value) catsData[this.value].forEach(v => {
            let o = document.createElement('option'); o.value = o.text = v; sub.appendChild(o);
        });
    });

    // 2. Cover Image Preview
    document.getElementById('coverInput').addEventListener('change', function(e) {
        const container = document.getElementById('coverPreview');
        container.innerHTML = '';
        if (this.files[0]) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(this.files[0]);
            container.appendChild(img);
        }
    });

    // 3. Gallery Images Preview (Allow up to 4)
    document.getElementById('galleryInput').addEventListener('change', function(e) {
        const container = document.getElementById('galleryPreview');
        container.innerHTML = '';
        if (this.files.length > 4) {
            alert("Maximum 4 images allowed for gallery.");
            this.value = "";
            return;
        }
        Array.from(this.files).forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            container.appendChild(img);
        });
    });

    // 4. Video Preview
    document.getElementById('videoInput').addEventListener('change', function(e) {
        const video = document.getElementById('videoPreview');
        if (this.files[0]) {
            video.src = URL.createObjectURL(this.files[0]);
            video.style.display = 'block';
            video.play();
        } else {
            video.style.display = 'none';
        }
    });
</script>