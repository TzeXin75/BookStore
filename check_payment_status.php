<?php
session_start();
header('Content-Type: application/json');
$ref = $_GET['ref'] ?? '';
if (isset($_SESSION['paid_refs']) && in_array($ref, $_SESSION['paid_refs'])) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'pending']);
}