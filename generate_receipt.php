<?php
// Generates and streams a PDF receipt for an order using FPDF.
require('includes/fpdf.php'); 
require_once 'config/db_connect.php';

// Ensure a session exists to determine access rights
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine logged-in user and role (app uses different session shapes in places)
if (isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
    $user_role = $_SESSION['user']['user_role'] ?? 'member';
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'member';
} else {
    // If not logged in, prevent download
    die("Error: Please log in to download receipts.");
}

$order_id = $_GET['id'] ?? 0;

// Security: admins can fetch any order; non-admins only their own
if ($user_role === 'admin') {
    $sql = "SELECT o.*, u.username, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.user_id 
            WHERE o.order_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id]);
} else {
    $sql = "SELECT o.*, u.username, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.user_id 
            WHERE o.order_id = ? AND o.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $user_id]);
}

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    // No matching order or access denied
    die("Error: Order not found or access denied.");
}

// Load all order items for the PDF
$sql_items = "SELECT od.*, b.title 
              FROM order_details od 
              JOIN book b ON od.id = b.id 
              WHERE od.order_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Small PDF subclass to add header/footer text
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'OFFICIAL RECEIPT', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 5, 'SIX SEVEN BS', 0, 1, 'C');
        $this->Ln(10);
    }

    // Simple footer showing page numbers
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Build the PDF content: header section with order metadata
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(35, 8, 'Order ID:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, '#' . $order['order_id'], 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(35, 8, 'Order Date:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, date('d M Y, H:i', strtotime($order['order_date'])), 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(35, 8, 'Member:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, htmlspecialchars($order['username']) . ' (' . htmlspecialchars($order['email']) . ')', 0, 1);

$pdf->Ln(10);

// Table header for items
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(110, 10, ' Item Description', 1, 0, 'L', true);
$pdf->Cell(30, 10, 'Qty', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Subtotal ', 1, 1, 'R', true);

// Items: iterate and render each line item, also accumulate subtotal
$pdf->SetFont('Arial', '', 10);
$calculated_subtotal = 0;

foreach ($items as $item) {
    $line_total = $item['quantity'] * $item['unit_price'];
    $calculated_subtotal += $line_total;
    
    // Truncate long titles to fit PDF layout
    $title = (strlen($item['title']) > 55) ? substr($item['title'], 0, 52) . '...' : $item['title'];

    $pdf->Cell(110, 10, ' ' . $title, 1);
    $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(50, 10, 'RM' . number_format($line_total, 2) . ' ', 1, 1, 'R');
}

// Totals and optional discount row
$pdf->Ln(5);

$grand_total = $order['total_amount'];
$discount_val = $calculated_subtotal - $grand_total;

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(140, 8, 'Subtotal:', 0, 0, 'R');
$pdf->Cell(50, 8, 'RM' . number_format($calculated_subtotal, 2), 0, 1, 'R');

if ($discount_val > 0.01) {
    // If a discount was applied, show it as a red negative line
    $pdf->SetTextColor(200, 0, 0);
    $pdf->Cell(140, 8, 'Voucher Discount:', 0, 0, 'R');
    $pdf->Cell(50, 8, '-RM' . number_format($discount_val, 2), 0, 1, 'R');
    $pdf->SetTextColor(0, 0, 0);
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 10, 'Grand Total (Paid):', 0, 0, 'R');
$pdf->Cell(50, 10, 'RM' . number_format($grand_total, 2), 1, 1, 'R');

// Send PDF to browser as a download
$pdf->Output('D', 'Receipt_Order_' . $order_id . '.pdf');
?>