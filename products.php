<?php
require_once 'db.php';

// --- 0. CONFIGURATION ---
// Define the low stock threshold for the filter
$low_stock_threshold = 50; 

// --- 1. SETUP SEARCH & FILTER ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$stock_alert = isset($_GET['stock_alert']) ? $_GET['stock_alert'] : ''; // Capture stock alert filter

// Build the SQL WHERE clause dynamically
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

// NEW: Apply Low Stock Alert Filter
if ($stock_alert === 'low') {
    $where_clauses[] = "stock <= :low_stock_threshold";
    $params['low_stock_threshold'] = $low_stock_threshold;
}

// Convert clauses to SQL string
$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// --- 2. PAGINATION ---
$limit = 10;
$page_no = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1; 
$offset = ($page_no - 1) * $limit;

// Count total rows based on filter
$count_sql = "SELECT COUNT(*) FROM book $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// --- 3. FETCH BOOKS ---
$sql = "SELECT * FROM book $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct categories for the dropdown
$catStmt = $pdo->query("SELECT DISTINCT category FROM book ORDER BY category");
$dbCategories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="product-page" style="margin: 0; padding: 0;">
    
    <div class="product-header" style="align-items: flex-end; margin-bottom: 20px;">
        <div>
            <h2>Products</h2>
            <p style="margin: 0; color: #666; font-size: 0.9rem;">
                Found <?= $total_rows ?> result(s)
            </p>
        </div>
        <a href="?page=add_product" class="btn primary" style="width: auto; display: inline-block;">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <div class="card" style="margin-bottom: 20px; background: #f8f9fa; border: 1px solid #e5e7eb; box-shadow: none;">
        <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="page" value="products">
            
            <input type="text" name="search" placeholder="Search title, author..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; min-width: 200px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin: 0;">
            
            <select name="category" style="width: 150px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin: 0;">
                <option value="">All Categories</option>
                <?php foreach ($dbCategories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category_filter ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="stock_alert" style="width: 150px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin: 0;">
                <option value="">In-Stock</option>
                <option value="low" <?= $stock_alert === 'low' ? 'selected' : '' ?>>
                    Low Stock Alert 
                </option>
            </select>
            
            <button type="submit" class="btn primary" style="width: auto; padding: 8px 15px; margin: 0;">
                <i class="fas fa-search"></i> Filter
            </button>
            
            <?php if($search || $category_filter || $stock_alert): ?>
                <a href="?page=products" class="btn secondary" style="padding: 8px 15px; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 80px;">Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($books): ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= $book['id'] ?></td>
                            <td>
                                <?php 
                                // Image path resolution
                                $img = 'https://via.placeholder.com/50';
                                if (!empty($book['images'])) {
                                    $parts = explode(',', $book['images']);
                                    $path = 'uploads/' . trim($parts[0]);
                                    if (file_exists($path)) $img = $path;
                                }
                                ?>
                                <img src="<?= $img ?>" alt="img" style="width: 40px; height: 50px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($book['title']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: #888;"><?= htmlspecialchars($book['author']) ?></span>
                            </td>
                            <td>
                                <span style="background: #eef2ff; color: #4f46e5; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">
                                    <?= htmlspecialchars($book['category']) ?>
                                    <small style="color:#888;">(<?= htmlspecialchars($book['subcategory']) ?>)</small>
                                </span>
                            </td>
                            <td style="color: #b03030; font-weight: bold;">RM <?= number_format($book['price'], 2) ?></td>
                            <td>
                                <?php 
                                // NEW: Highlight based on the defined threshold (50)
                                $stock_display = $book['stock'];
                                $stock_style = '';
                                if ($book['stock'] <= $low_stock_threshold) {
                                    $stock_style = 'color: #ef4444; font-weight: bold; padding: 2px 5px; border-radius: 4px;'; // Red/Alert Style
                                    
                                }
                                ?>
                                <span style="<?= $stock_style ?>"><?= $stock_display ?></span>
                            </td>
                            <td>
                                <a href="?page=edit_product&id=<?= $book['id'] ?>" class="btn secondary" style="padding: 5px 10px; font-size: 0.8rem;">Edit</a>
                                <a href="delete_product.php?id=<?= $book['id'] ?>" class="btn delete" style="padding: 5px 10px; font-size: 0.8rem; background: #fee2e2; color: #991b1b; margin-left: 5px;" onclick="return confirm('Delete this book?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">No products found matching your search.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php 
            // Rebuild query string for pagination links
            $queryParams = $_GET; 
            unset($queryParams['p']); // Remove old page number
            $queryString = http_build_query($queryParams);
            ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?= $queryString ?>&p=<?= $i ?>" class="<?= $i == $page_no ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>