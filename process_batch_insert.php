<?php
// process_batch_insert.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['titles'])) {
    $titles = $_POST['titles'];
    $authors = $_POST['authors'];
    $categories = $_POST['categories'];
    $subcategories = $_POST['subcategories']; 
    $prices = $_POST['prices'];
    $stocks = $_POST['stocks'];

    try {
        $pdo->beginTransaction();
        
        // Updated SQL to include subcategory placeholder
        $stmt = $pdo->prepare("INSERT INTO book (title, author, category, subcategory, price, stock, cover_image) 
                               VALUES (?, ?, ?, ?, ?, ?, 'download.svg')");

        for ($i = 0; $i < count($titles); $i++) {
            if (!empty(trim($titles[$i]))) {
                $stmt->execute([
                    $titles[$i],
                    $authors[$i],
                    $categories[$i],
                    $subcategories[$i], 
                    $prices[$i],
                    $stocks[$i]
                ]);
            }
        }

        $pdo->commit();
        echo "<script>alert('Batch insert successful!'); window.location.href='admin.php?page=product_dir';</script>";
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Batch Insert Failed: " . $e->getMessage());
    }
} else {
    header("Location: admin.php?page=product_dir");
    exit();
}