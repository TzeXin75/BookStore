<?php
// 1. DATABASE ACCESS DETAILS
// These variables tell PHP where the database is and how to log in
$host = "localhost";      // Usually 'localhost' if the database is on the same server
$dbname = "bookstore";    // The name of the database
$username = "root";       // The default username for local servers (XAMPP/WAMP)
$password = "";           // The default password is empty for local servers

// 2. THE CONNECTION ATTEMPT
// use a 'try' block because database connections can sometimes fail
try {
    // Create the connection object (PDO) and set the text encoding to UTF-8
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // 3. ERROR SETTINGS
    // Tell PDO to show an "Exception" (a clear error message) if a SQL query fails
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // 4. ERROR HANDLING
    // If the connection fails (e.g., wrong password), stop the script and show why
    echo "Connection failed: " . $e->getMessage();
}
?>