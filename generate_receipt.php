<?php
// generate_receipt.php
require('includes/fpdf.php'); 
require_once 'config/db_connect.php';

// 1. Get Order ID
if (!isset($_GET['id'])) {
    die("Error: Order ID is missing.");
}
$order_id = $_GET['id'];

// 2. Fetch Order Details (Header)
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Error: Order not found.");
}

// 3. Fetch Order Items
$sql_items = "SELECT od.*, b.title 
              FROM order_details od 
              JOIN book b ON od.book_id = b.id 
              WHERE od.order_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// 4. GENERATE PDF
class PDF extends FPDF {
    // Page Header
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'OFFICIAL RECEIPT',0,1,'C');
        $this->SetFont('Arial','I',10);
        $this->Cell(0,5,'Online Book Store Sdn Bhd',0,1,'C');
        $this->Ln(10); // Line break
    }

    // Page Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

// Create PDF Object
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// --- ORDER INFO ---
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40, 10, 'Order ID:', 0, 0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0, 10, '#' . $order['order_id'], 0, 1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40, 10, 'Date:', 0, 0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0, 10, $order['order_date'], 0, 1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40, 10, 'Customer:', 0, 0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0, 10, $order['username'] . ' (' . $order['email'] . ')', 0, 1);

$pdf->Ln(10); // Space

// --- TABLE HEADER ---
$pdf->SetFillColor(200, 220, 255);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(110, 10, 'Book Title', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Subtotal', 1, 1, 'C', true);

// --- TABLE ROWS ---
$pdf->SetFont('Arial','',11);
foreach ($items as $item) {
    $subtotal = $item['quantity'] * $item['unit_price'];
    
    // Title (Trim if too long)
    $title = $item['title'];
    if (strlen($title) > 50) { 
        $title = substr($title, 0, 47) . '...'; 
    }

    $pdf->Cell(110, 10, ' ' . $title, 1);
    $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(50, 10, '$' . number_format($subtotal, 2), 1, 1, 'R');
}

// --- TOTAL ---
$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(140, 10, 'Grand Total (Paid):', 0, 0, 'R');
$pdf->Cell(50, 10, '$' . number_format($order['total_amount'], 2), 1, 1, 'R');

//download file to computer instead of opening a new tab in ur broswer
$pdf->Output('D', 'Receipt_'.$order_id.'.pdf');
?>