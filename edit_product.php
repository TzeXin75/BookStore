<?php
require_once 'db.php';

// Allowed categories and subcategories
$categories = [
    'Fiction' => ['Novel','Comic'],
    'Non-Fiction' => ['Biography','Education'],
    'Education' => ['Textbook'],
    'Children' => ['Color Book']
];

// Get product id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product info
$stmt = $pdo->prepare("SELECT * FROM book WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $category = $_POST['category'] ?? '';
    $subcategory = $_POST['subcategory'] ?? '';
    $language = trim($_POST['language'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    // --- LOGIC: IMAGE HANDLING & CLEANUP (MODIFIED for 4-image limit) ---
$images = $product['images']; // Default: Keep current images
$upload_success = true;

// Only run this if the user actually selected NEW files
if (isset($_FILES['images']) && $_FILES['images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
    
    $new_uploaded_images = [];
    
    // --- NEW: 1. Count and Validate Upload Limit ---
    $totalFilesToProcess = 0;
    // Count the files that were successfully received by the server (error code UPLOAD_ERR_OK)
    foreach ($_FILES['images']['error'] as $error) {
        if ($error === UPLOAD_ERR_OK) {
            $totalFilesToProcess++;
        }
    }
    
    // Check for the 4 image limit
    if ($totalFilesToProcess > 4) {
        $upload_success = false;
        // Stop processing and set an error message
        $errors[] = "You can only upload a maximum of 4 images for the product.";
        
    } else {
        // --- OLD: 2. Upload the new images (Only if limit check passed) ---
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
            // Check file-specific error again before proceeding
            if ($_FILES['images']['error'][$k] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext; // Unique name
                
                if (!is_dir('uploads')) mkdir('uploads', 0777, true);

                if (move_uploaded_file($tmp_name, "uploads/$filename")) {
                    $new_uploaded_images[] = $filename;
                } else {
                    $upload_success = false;
                    $errors[] = "Failed to upload image: " . $_FILES['images']['name'][$k];
                }
            }
        }
    }


    // --- OLD: 3. Delete Old Images and Update DB String (Only if new upload worked) ---
    if ($upload_success && !empty($new_uploaded_images)) {
        
        // This check ensures we don't proceed with deletion/update if the limit failed
        if (empty($errors)) { 
             if (!empty($product['images'])) {
                $old_images_list = explode(',', $product['images']);
                foreach ($old_images_list as $old_img) {
                    $file_path = "uploads/" . trim($old_img);
                    if (file_exists($file_path)) {
                        unlink($file_path); // Delete the physical file
                    }
                }
            }
            // Update the database string to the new images
            $images = implode(',', $new_uploaded_images);
        }
    }
}
// --- END IMAGE LOGIC ---

    // Validation
    if (!$title) $errors[] = "Title is required.";
    if (!isset($categories[$category])) $errors[] = "Invalid category.";
    if ($subcategory && !in_array($subcategory, $categories[$category])) $errors[] = "Invalid subcategory.";
    if ($price <= 0) $errors[] = "Price must be greater than 0.";
    if ($stock < 0) $errors[] = "Stock cannot be negative.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE book SET title=?, description=?, author=?, publisher=?, category=?, subcategory=?, language=?, price=?, stock=?, images=? WHERE id=?");
        $stmt->execute([$title, $description, $author, $publisher, $category, $subcategory, $language, $price, $stock, $images, $id]);
        $success = "✅ Product updated successfully!";
        
        // Refresh product info to show the new data immediately
        $stmt = $pdo->prepare("SELECT * FROM book WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<div class="product-page" style="margin: 0; padding: 0;">
    <h2 style="margin-bottom: 20px;">Edit Product: <?= htmlspecialchars($product['title']) ?></h2>

    <?php if ($errors): ?>
        <div class="error-box">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-box"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="product-form" style="display: flex; gap: 20px;">
        <div class="product-left">
            <div class="card">
                <h3>Basic Info</h3>
                <label>Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>

                <label>Description</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="card">
                <h3>Author & Publisher</h3>
                <label>Author</label>
                <input type="text" name="author" value="<?= htmlspecialchars($product['author']) ?>">

                <label>Publisher</label>
                <input type="text" name="publisher" value="<?= htmlspecialchars($product['publisher']) ?>">
            </div>

            <div class="card">
                <h3>Category</h3>
                <label>Category</label>
                <select name="category" id="categorySelect" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat => $subs): ?>
                        <option value="<?= $cat ?>" <?= $product['category']==$cat?'selected':'' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Subcategory</label>
                <select name="subcategory" id="subcategorySelect">
                    <option value="">Select Subcategory</option>
                </select>
            </div>
        </div>

        <div class="product-right">
            <div class="card">
                <h3>Other Info</h3>
                <label>Language</label>
                <?php $allowed_languages = ['English', 'Chinese', 'Malay'];?>
                <select name="language" required>
                    <option value="">Select Language</option>
                    
                    <?php foreach ($allowed_languages as $lang): ?>
                        <?php $selected = ($language === $lang) ? 'selected' : ''; ?>
                        
                        <option value="<?= htmlspecialchars($lang) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Price (RM)</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>">

                <label>Stock</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>">
            </div>

            <div class="card">
                <h3>Images</h3>
                
                <?php if (!empty($product['images'])): ?>
                    <label class="preview-label">Current Images:</label>
                    <div class="current-images" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                        <?php 
                        $imgs = explode(',', $product['images']);
                        foreach ($imgs as $img): ?>
                            <img src="uploads/<?= htmlspecialchars($img) ?>" alt="Product Image" style="width: 80px; height: 100px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:#999; font-size:0.9rem;">No images uploaded.</p>
                <?php endif; ?>

                <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">

                <label>Replace Images</label>
                <input type="file" name="images[]" multiple id="imagesInput">
                <p class="preview-label">Uploading new files will replace the current ones.</p>
                
                <div id="new-preview" class="current-images"></div>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary">Update Product</button>
            </div>
        </div>
    </form>
</div>

<script>
// --- 1. Dynamic Subcategories ---
const categories = <?= json_encode($categories) ?>;
const categorySelect = document.getElementById('categorySelect');
const subcategorySelect = document.getElementById('subcategorySelect');

function updateSubcategories(selected=null) {
    const cat = categorySelect.value;
    subcategorySelect.innerHTML = '';
    
    if (!cat || !categories[cat]) {
        subcategorySelect.disabled = true;
        subcategorySelect.innerHTML = '<option value="">Select Category First</option>';
        return;
    }
    
    subcategorySelect.disabled = false;
    categories[cat].forEach(sub => {
        const opt = document.createElement('option');
        opt.value = sub;
        opt.text = sub;
        if (selected && selected === sub) opt.selected = true;
        subcategorySelect.appendChild(opt);
    });
}

// Initialize on load
updateSubcategories("<?= $product['subcategory'] ?>");

// Update on change
categorySelect.addEventListener('change', () => {
    updateSubcategories();
});

// --- 2. New Image Preview ---
const imagesInput = document.getElementById('imagesInput');
const previewContainer = document.getElementById('new-preview');

if (imagesInput) {
    imagesInput.addEventListener('change', () => {
        previewContainer.innerHTML = ''; // Clear previous preview
        const files = imagesInput.files;
        
        if (files.length > 0) {
            Array.from(files).forEach(file => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.style.width = '80px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '5px';
                img.style.border = '1px solid #ddd';
                previewContainer.appendChild(img);
            });
        }
    });
}
</script>