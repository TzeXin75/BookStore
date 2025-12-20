<?php
require('includes/fpdf.php'); 
require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Prevent unauthorized downloads
if (!isset($_SESSION['user_id'])) {
    die("Error: Please log in to download receipts.");
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// 1. Fetch Order Header (Verify it belongs to the logged-in user)
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = ? AND o.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Error: Order not found or access denied.");
}

// 2. FIX: Fetch Items using the correct column 'id' instead of 'book_id'
$sql_items = "SELECT od.*, b.title 
              FROM order_details od 
              JOIN book b ON od.id = b.id 
              WHERE od.order_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// 3. GENERATE PDF
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'OFFICIAL RECEIPT', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 5, 'Online Book Store Sdn Bhd', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Order Info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Order ID:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, '#' . $order['order_id'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Date:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, date('d M Y, H:i', strtotime($order['order_date'])), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Customer:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, htmlspecialchars($order['username']) . ' (' . htmlspecialchars($order['email']) . ')', 0, 1);

$pdf->Ln(10);

// Table Header
$pdf->SetFillColor(230, 230, 230);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(110, 10, 'Book Title', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Subtotal', 1, 1, 'C', true);

// Table Rows
$pdf->SetFont('Arial', '', 11);
foreach ($items as $item) {
    $subtotal = $item['quantity'] * $item['unit_price'];
    $title = (strlen($item['title']) > 50) ? substr($item['title'], 0, 47) . '...' : $item['title'];

    $pdf->Cell(110, 10, ' ' . $title, 1);
    $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(50, 10, '$' . number_format($subtotal, 2), 1, 1, 'R');
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(140, 10, 'Grand Total (Paid):', 0, 0, 'R');
$pdf->Cell(50, 10, '$' . number_format($order['total_amount'], 2), 1, 1, 'R');

$pdf->Output('D', 'Receipt_' . $order_id . '.pdf');
?>