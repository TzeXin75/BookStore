<?php
//start session
session_start();

//JSON data object
header('Content-Type: application/json');

//receive reference ID from AJAX request
$ref = $_GET['ref'] ?? '';

//check for specific reference 
if (isset($_SESSION['paid_refs']) && in_array($ref, $_SESSION['paid_refs'])) {
    
    //when reference is found , automatic form is submitted to checkout page
    echo json_encode(['status' => 'success']);
} else {
    //when reference is not found , payment is still pending
    echo json_encode(['status' => 'pending']);
}