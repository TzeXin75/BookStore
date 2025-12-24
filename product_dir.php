<?php
// --- 1. SETUP, DATABASE & SECURITY ---
require_once 'config/db_connect.php';

// Ensure the session is started and user is an admin
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Map the DB connection if shell uses a different variable name
if (isset($_db) && !isset($pdo)) { $pdo = $_db; }

$defaultBookImage = "uploads/download.svg";

// --- 2. CONFIGURATION ---
$low_stock_threshold = 50; 

// --- 3. SEARCH & FILTER LOGIC ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$stock_alert = isset($_GET['stock_alert']) ? $_GET['stock_alert'] : ''; 

$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(title LIKE :search OR author LIKE :search OR publisher LIKE :search)";
    $params['search'] = "%$search%";
}

if ($category_filter) {
    $where_clauses[] = "category = :category";
    $params['category'] = $category_filter;
}

if ($stock_alert === 'low') {
    $where_clauses[] = "stock <= :low_stock_threshold";
    $params['low_stock_threshold'] = $low_stock_threshold;
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// --- 4. PAGINATION CALCULATIONS ---
$limit = 10;
$page_no = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1; 
$offset = ($page_no - 1) * $limit;

$count_sql = "SELECT COUNT(*) FROM book $where_sql";
$countStmt = $pdo->prepare($count_sql);
$countStmt->execute($params);
$total_rows = $countStmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// --- 5. FETCH DATA (ORDER BY id DESC ensures latest is first) ---
$sql = "SELECT * FROM book $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dbCategories = $pdo->query("SELECT DISTINCT category FROM book ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// --- IMAGE HELPER FUNCTION ---
function getAdminDisplayImage($book, $defaultPath) {
    // 1. Try Primary Cover first
    if (!empty($book['cover_image'])) {
        $path = 'uploads/' . $book['cover_image'];
        if (file_exists($path)) return $path;
    }
    
    // 2. Fallback to first image in Gallery
    if (!empty($book['images'])) {
        $imgs = explode(',', $book['images']);
        $path = 'uploads/' . trim($imgs[0]);
        if (!empty($imgs[0]) && file_exists($path)) return $path;
    }
    
    // 3. Final fallback to placeholder
    return $defaultPath;
}

?>

<script>
    function toggleSelectAll(source) {
        const checkboxes = document.getElementsByName('ids[]');
        for (let i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
    }
</script>

<div class="product-page" style="margin: 0; padding: 20px;">
    
    <div class="product-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0; color: #2c3e50;">Product Management</h2>
            <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">
                Displaying <?= count($books) ?> of <?= $total_rows ?> total record(s)
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" form="batchDeleteForm" style="padding: 10px 20px; background: #dc2626; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.9rem;">
                Delete Selected
            </button>
            <a href="admin.php?page=add_product" class="btn-primary" style="padding: 10px 20px; text-decoration: none; background: #2563eb; color: white; border-radius: 6px; font-weight: bold; font-size: 0.9rem;">
                Add New Product
            </a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px; background: #f8f9fa; border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px;">
        <form method="GET" action="admin.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="page" value="product_dir">
            
            <input type="text" name="search" placeholder="Search title, author..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px;">
            
            <select name="category" style="width: 180px; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px;">
                <option value="">All Categories</option>
                <?php foreach ($dbCategories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category_filter ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="stock_alert" style="width: 180px; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px;">
                <option value="">Show All Stock</option>
                <option value="low" <?= $stock_alert === 'low' ? 'selected' : '' ?>>
                    Low Stock Alert (≤ <?= $low_stock_threshold ?>)
                </option>
            </select>
            
            <button type="submit" style="background: #2c3e50; border: none; color: white; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600;">Filter</button>
            
            <?php if($search || $category_filter || $stock_alert): ?>
                <a href="admin.php?page=product_dir" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; font-weight: 600; margin-left: 10px;">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>

    <form id="batchDeleteForm" action="batch_delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete all selected products?')">
        <div class="card" style="padding: 0; overflow: hidden; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 12px; width: 40px; text-align: left;">
                            <input type="checkbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #4b5563;">ID</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #4b5563;">Cover</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #4b5563;">Book Details</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #4b5563;">Category</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #4b5563;">Price</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.85rem; color: #4b5563;">Stock</th>
                        <th style="padding: 12px; text-align: center; font-size: 0.85rem; color: #4b5563;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books): ?>
                        <?php foreach ($books as $book): ?>
                            <?php 
                                // Logic for Low Stock Coloring
                                $isLowStock = ($book['stock'] <= $low_stock_threshold);
                                $stockColor = $isLowStock ? '#ef4444' : '#111827';
                            ?>
                            <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 12px;">
                                    <input type="checkbox" name="ids[]" value="<?= $book['id'] ?>">
                                </td>
                                <td style="padding: 12px; font-size: 0.9rem;"><?= $book['id'] ?></td>
                                <td style="padding: 12px;">
                                    <?php 
                                        $img = $defaultBookImage;
                                        if (!empty($book['cover_image']) && file_exists('uploads/' . $book['cover_image'])) {
                                            $img = 'uploads/' . $book['cover_image'];
                                        }
                                    ?>
                                    <img src="<?= $img ?>" style="width: 45px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;">
                                </td>
                                <td style="padding: 12px;">
                                    <strong style="color: #111827; font-size: 0.95rem;"><?= htmlspecialchars($book['title']) ?></strong><br>
                                    <span style="font-size: 0.8rem; color: #6b7280;">by <?= htmlspecialchars($book['author']) ?></span>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="background: #eef2ff; color: #4f46e5; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                        <?= htmlspecialchars($book['category']) ?>
                                        <?php if (!empty($book['subcategory'])): ?>
                                            <span style="text-transform: none; color: #6366f1; opacity: 0.8;">
                                                (<?= htmlspecialchars($book['subcategory']) ?>)
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; font-weight: bold;">RM <?= number_format($book['price'], 2) ?></td>
                                <td style="padding: 12px; color: <?= $stockColor ?>; font-weight: <?= $isLowStock ? 'bold' : 'normal' ?>;">
                                    <?= $book['stock'] ?>
                                    <?= $isLowStock ? ' ⚠️' : '' ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="admin.php?page=edit_product&id=<?= $book['id'] ?>" style="color: #2563eb; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="padding: 40px; text-align: center; color: #6b7280;">No products found matching filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <?php if ($total_pages > 1): ?>
        <div class="pagination" style="display: flex; justify-content: center; margin-top: 30px; gap: 8px;">
            <?php 
                $queryParams = $_GET; 
                unset($queryParams['p']); 
                $queryString = http_build_query($queryParams);
            ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="admin.php?<?= $queryString ?>&p=<?= $i ?>" 
                   style="padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 0.9rem; 
                   <?= $i == $page_no ? 'background: #2563eb; color: white;' : 'background: #fff; color: #4b5563; border: 1px solid #d1d5db;' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>