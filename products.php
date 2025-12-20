<?php
require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Security: Ensure only admins see this page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$low_stock_threshold = 50; 
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$stock_alert = $_GET['stock_alert'] ?? '';

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

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$limit = 10;
$page_no = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1; 
$offset = ($page_no - 1) * $limit;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM book $where_sql");
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM book $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dbCategories = $pdo->query("SELECT DISTINCT category FROM book ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>

<div class="product-page" style="max-width: 1200px; margin: auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Product Maintenance</h2>
        <a href="add_product.php" class="btn-primary" style="padding: 10px 15px; text-decoration: none; background: #28a745; color: white; border-radius: 4px;">+ Add New Book</a>
    </div>

    <form method="GET" style="display: flex; gap: 10px; margin-bottom: 20px; background: #f4f4f4; padding: 15px; border-radius: 8px;">
        <input type="text" name="search" placeholder="Search title or author..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; padding: 8px;">
        <select name="category" style="padding: 8px;">
            <option value="">All Categories</option>
            <?php foreach ($dbCategories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category_filter ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="padding: 8px 15px; cursor: pointer;">Filter</button>
    </form>

    <table style="width: 100%; border-collapse: collapse; background: white;">
        <thead style="background: #2c3e50; color: white;">
            <tr>
                <th style="padding: 12px; text-align: left;">ID</th>
                <th style="padding: 12px; text-align: left;">Title</th>
                <th style="padding: 12px; text-align: left;">Price</th>
                <th style="padding: 12px; text-align: left;">Stock</th>
                <th style="padding: 12px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 12px;"><?= $book['id'] ?></td>
                    <td style="padding: 12px;"><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                    <td style="padding: 12px;">$<?= number_format($book['price'], 2) ?></td>
                    <td style="padding: 12px; color: <?= ($book['stock'] <= 5) ? 'red' : 'black' ?>;">
                        <?= $book['stock'] ?>
                    </td>
                    <td style="padding: 12px;">
                        <a href="edit_product.php?id=<?= $book['id'] ?>" style="color: #3498db;">Edit</a> | 
                        <a href="delete_product.php?id=<?= $book['id'] ?>" style="color: #e74c3c;" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>