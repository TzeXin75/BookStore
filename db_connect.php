<?php
// config/db_connect.php

$host = "localhost";
$username = "root";       // Default XAMPP username
$password = "";           // Default XAMPP password (leave empty)
$dbname = "bookstore"; // Make sure this matches your PHPMyAdmin database name

try {
    // Creating a new PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Setting Error Mode to Exception (Helps debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Uncomment the line below only to test if it works, then comment it out again.
     //echo "Database Connected Successfully!"; 
    
} catch(PDOException $e) {
    // If connection fails, stop everything and show error
    die("Connection failed: " . $e->getMessage());
}
?>