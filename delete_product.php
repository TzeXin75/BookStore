<?php
require_once 'db.php';

// Check if ID is provided in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 1. Get the image filename(s) first so we can delete the file later
    $stmt = $pdo->prepare("SELECT images FROM book WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        // 2. Delete the record from the database
        $deleteStmt = $pdo->prepare("DELETE FROM book WHERE id = ?");
        
        if ($deleteStmt->execute([$id])) {
            // 3. If DB delete was successful, delete the actual image files from the folder
            if (!empty($book['images'])) {
                $imagesList = explode(',', $book['images']);
                foreach ($imagesList as $img) {
                    $filePath = "uploads/" . trim($img);
                    if (file_exists($filePath)) {
                        unlink($filePath); // This function deletes the file
                    }
                }
            }
        }
    }
}

// Redirect back to the products list
header("Location: admin.php?page=products");
exit;
?>