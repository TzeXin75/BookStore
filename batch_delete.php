<?php
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Security: Check if admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_role'] !== 'admin') {
    die("Unauthorized access.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids'])) {
    $ids = $_POST['ids']; // Array of IDs from the checkboxes
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    try {
        // 2. Fetch all media filenames first todelete them from the server
        $stmt = $pdo->prepare("SELECT cover_image, images, video FROM book WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $books_to_delete = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($books_to_delete as $book) {
            // Delete Cover
            if (!empty($book['cover_image']) && file_exists("uploads/" . $book['cover_image'])) {
                unlink("uploads/" . $book['cover_image']);
            }
            // Delete Gallery Images
            if (!empty($book['images'])) {
                $imgs = explode(',', $book['images']);
                foreach ($imgs as $img) {
                    if (file_exists("uploads/" . trim($img))) unlink("uploads/" . trim($img));
                }
            }
            // Delete Video
            if (!empty($book['video']) && file_exists("uploads/" . $book['video'])) {
                unlink("uploads/" . $book['video']);
            }
        }

        // 3. Delete from Database
        $delStmt = $pdo->prepare("DELETE FROM book WHERE id IN ($placeholders)");
        $delStmt->execute($ids);

        $count = count($ids);
        header("Location: admin.php?page=product_dir&deleted_title=" . urlencode("$count items"));
        exit();

    } catch (Exception $e) {
        die("Error during batch delete: " . $e->getMessage());
    }
} else {
    header("Location: admin.php?page=product_dir");
    exit();
}