<?php
// CONNECT TO DATABASE
require_once 'db.php';

// CHECK IF THE URL HAS AN ID (e.g., delete.php?id=10)
if (isset($_GET['id'])) {
    // Convert the ID to a number for safety
    $id = intval($_GET['id']);

    // --- STEP 1: FIND THE IMAGES FIRST ---
    // need to know the filenames before delete the database record
    $stmt = $pdo->prepare("SELECT images FROM book WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        // --- STEP 2: DELETE FROM DATABASE ---
        // Remove the book row from the 'book' table
        $deleteStmt = $pdo->prepare("DELETE FROM book WHERE id = ?");
        
        if ($deleteStmt->execute([$id])) {
            
            // --- STEP 3: CLEAN UP STORAGE FOLDER ---
            // If the database record is gone,  don't need the photo files anymore
            if (!empty($book['images'])) {
                // Since images are stored as a list (image1.jpg, image2.jpg), we split them
                $imagesList = explode(',', $book['images']);
                
                foreach ($imagesList as $img) {
                    $filePath = "uploads/" . trim($img);
                    
                    // Check if the file actually exists on the computer disk
                    if (file_exists($filePath)) {
                        // 'unlink' is the PHP command to permanently delete a file
                        unlink($filePath); 
                    }
                }
            }
        }
    }
}

// --- STEP 4: GO BACK ---
// After finishing, automatically send the admin back to the product list page
header("Location: admin.php?page=products");
exit;
?>