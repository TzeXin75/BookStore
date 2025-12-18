<?php
session_start();
session_destroy();
echo "Cart has been reset! <a href='index.php'>Go back to Shop</a>";
?>