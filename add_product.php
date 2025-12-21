<?php
require_once 'db_connet.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Allowed categories
$categories = [
    'Fiction' => ['Novel','Comic'],
    'Non-Fiction' => ['Biography','Self-help'],
    'Education' => ['Textbook'],
    'Children' => ['Color Book']
];

$errors = [];
$success = '';

// Initialize variables to keep form data if error occurs
$title = '';
$description = '';
$author = '';
$publisher = '';
$cat_val = '';
$sub_val = '';
$language = '';
$price = '';
$stock = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $cat_val = $_POST['category'] ?? '';
    $sub_val = $_POST['subcategory'] ?? '';
    $language = trim($_POST['language'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    // --- IMAGE UPLOAD LOGIC (MODIFIED for 4-image limit) ---
    $imageNames = [];
    if (isset($_FILES['images'])) {
        
        // 1. Get the total number of files attempted for upload
        $totalFilesToProcess = count($_FILES['images']['tmp_name']);

        // 2. CHECK LIMIT: If more than 4 images are uploaded, add an error and skip processing
        if ($totalFilesToProcess > 4) {
            $errors[] = "You can only upload a maximum of 4 images.";
            
            // Skip the file processing loop below and keep $imageNames empty.
            // The main PHP logic will halt the database insert due to the error.
            
        } else {
            // 3. Process the files since the count is within the limit (0 to 4)
            foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
                
                // Check for file-specific errors (e.g., file size limit)
                if ($_FILES['images']['error'][$k] === UPLOAD_ERR_OK) {
                    
                    // Get extension and generate a unique filename
                    $ext = pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $ext;
                    
                    // Create folder if not exists
                    if (!is_dir('uploads')) mkdir('uploads', 0777, true);

                    // Move the temporary file to the final destination
                    if (move_uploaded_file($tmp_name, "uploads/$filename")) {
                        $imageNames[] = $filename;
                    }
                }
            }
        }
    }
    $imagesStr = implode(',', $imageNames);
    // --- END IMAGE LOGIC ---

    // Validation
    if (!$title) $errors[] = "Title is required.";
    if (!isset($categories[$cat_val])) $errors[] = "Invalid category.";
    if ($sub_val && !in_array($sub_val, $categories[$cat_val])) $errors[] = "Invalid subcategory.";
    if ($price <= 0) $errors[] = "Price must be greater than 0.";
    if ($stock < 0) $errors[] = "Stock cannot be negative.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO book (title, description, author, publisher, category, subcategory, language, price, stock, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $author, $publisher, $cat_val, $sub_val, $language, $price, $stock, $imagesStr]);
            
            $success = "✅ Product added successfully!";
            
            // Clear form after success
            $title = $description = $author = $publisher = $cat_val = $sub_val = $language = $price = $stock = '';
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        /* Extra styling for image preview matching edit page */
        .current-images {
            display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;
        }
        .current-images img {
            width: 80px; height: 100px; object-fit: cover;
            border-radius: 5px; border: 1px solid #ddd;
        }
        .preview-label {
            font-size: 0.85rem; color: #6b7280; margin-top: 5px; display: block;
        }
    </style>
</head>
<body>



<div class="product-page">
    <h2>Add New Product</h2>

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

    <form method="POST" enctype="multipart/form-data" class="product-form">
        <div class="product-left">
            <div class="card">
                <h3>Basic Info</h3>
                <label>Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>

                <label>Description</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($description) ?></textarea>
            </div>

            <div class="card">
                <h3>Author & Publisher</h3>
                <label>Author</label>
                <input type="text" name="author" value="<?= htmlspecialchars($author) ?>">

                <label>Publisher</label>
                <input type="text" name="publisher" value="<?= htmlspecialchars($publisher) ?>">
            </div>

            <div class="card">
                <h3>Category</h3>
                <label>Category</label>
                <select name="category" id="categorySelect" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat => $subs): ?>
                        <option value="<?= $cat ?>" <?= $cat_val==$cat?'selected':'' ?>><?= $cat ?></option>
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
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>">

                <label>Stock</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($stock) ?>">
            </div>

            <div class="card">
                <h3>Images</h3>
                
                <label>Upload Images</label>
                <input type="file" name="images[]" multiple id="imagesInput">
                <p class="preview-label">Selected images will be uploaded.</p>
                
                <div id="new-preview" class="current-images"></div>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary">Add Product</button>
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

// Initialize on load (if validation failed, keep selection)
updateSubcategories("<?= $sub_val ?>");

// Update on change
categorySelect.addEventListener('change', () => {
    updateSubcategories();
});

// --- 2. New Image Preview ---
const imagesInput = document.getElementById('imagesInput');
const previewContainer = document.getElementById('new-preview');

if(imagesInput) {
    imagesInput.addEventListener('change', () => {
        previewContainer.innerHTML = ''; 
        const files = imagesInput.files;
        
        if (files.length > 0) {
            Array.from(files).forEach(file => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                previewContainer.appendChild(img);
            });
        }
    });
}

updateSubcategories("<?= $sub_val ?>"); 

// Update on change
categorySelect.addEventListener('change', () => {
    updateSubcategories();
});
</script>

</body>
</html>