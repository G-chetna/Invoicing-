<?php
// Start output buffering at the very beginning
ob_start();

include('Connection.php');
require('fpd/fpdf.php');

function createDirectory($dirPath) {
    if (!file_exists($dirPath)) {
        mkdir($dirPath, 0755, true);
    }
}

$pdfDirectory = 'InvoicesPDF';
define('PDF_STORAGE_DIR', $pdfDirectory);

createDirectory(PDF_STORAGE_DIR);

class PDF_Invoice extends FPDF {
    public $customerName;
    public $ein;
    public $email;
    public $voice;

    function Header() {
        $this->SetTextColor(150, 150, 150);
        $this->SetFont('Arial', 'B', 25);
    
        // Place the "INVOICE" text as a watermark at the top right
        $this->SetXY(140, 10);
        $this->Cell(0, 0, 'INVOICE', 0, 0, 'R');
    
        // Reset to original settings for the rest of the header
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 10);
               
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(10, 10);
        $this->Cell(0, 6, 'Advanced Image Inc.', 0, 1, 'L');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, '2517 Suncrest Dr', 0, 1, 'L');
        $this->Cell(0, 5, 'Garland, TX 75044', 0, 1, 'L');
        $this->Cell(0, 5, 'USA', 0, 1, 'L');
        
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(17, 6, 'Customer:', 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 6, ($this->customerName ?? 'N/A'), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(15, 6, 'EIN:', 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 6, ($this->ein ?? 'N/A'), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(15, 6, 'Email:', 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 6, ($this->email ?? 'N/A'), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(15, 6, 'Voice:', 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 6, ($this->voice ?? 'N/A'), 0, 1, 'L');
        
        $this->Ln(2);
    }

    function InvoiceDetails($invoiceNumber, $invoiceDate) {
        $this->SetFont('Arial', '', 10);
        $this->SetXY(-80, 16);
        $this->Cell(0, 6, "Invoice Number: $invoiceNumber", 0, 1, 'R');
        $this->SetXY(-80, 22);
        $this->Cell(0, 6, "Invoice Date: $invoiceDate", 0, 1, 'R');
    }

    function GetMultiCellHeight($w, $h, $txt) {
        $x = $this->GetX();
        $y = $this->GetY();
        
        $lines = 0;
        $currentLine = '';
        $words = explode(' ', str_replace("\n", ' ', $txt));
        
        foreach($words as $word) {
            $testLine = $currentLine . ' ' . $word;
            $testWidth = $this->GetStringWidth(trim($testLine));
            
            if($testWidth > $w) {
                $lines++;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        $lines++;
        
        $lines += substr_count($txt, "\n");
        
        $this->SetXY($x, $y);
        
        return $lines * $h;
    }

    function CompanyDetails($billTo, $shipTo) {
        $startY = 65;
        $this->SetY($startY);
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 10);
        
        $width = 92.5;
        
        $this->Cell($width, 6, 'Bill To:', 1, 0, 'L', true);
        $this->Cell(5);
        $this->Cell($width, 6, 'Ship To:', 1, 1, 'L', true);

        $this->SetFont('Arial', '', 10);
        $startX = $this->GetX();
        
        $billToHeight = $this->GetMultiCellHeight($width, 6, $billTo);
        $shipToHeight = $this->GetMultiCellHeight($width, 6, $shipTo);
        $maxHeight = max($billToHeight, $shipToHeight);

        $this->Rect($startX, $this->GetY(), $width, $maxHeight);
        $this->Rect($startX + $width + 5, $this->GetY(), $width, $maxHeight);

        $this->MultiCell($width, 6, $billTo, 0, 'L');
        $this->SetXY($startX + $width + 5, $startY + 6);
        $this->MultiCell($width, 6, $shipTo, 0, 'L');
        
        $this->SetY($startY + $maxHeight + 10);
    }

    function AdditionalDetails($paymentTerms, $shipDate, $dueDate, $shippingMethod) {
        $labelWidth = 45;
        $valueWidth = 50;
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($labelWidth, 6, "Payment Terms:", 1, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell($valueWidth, 6, $paymentTerms, 1, 0, 'L');
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($labelWidth, 6, "Ship Date:", 1, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell($valueWidth, 6, $shipDate, 1, 1, 'L');
    
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($labelWidth, 6, "Due Date:", 1, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell($valueWidth, 6, $dueDate, 1, 0, 'L');
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($labelWidth, 6, "Shipping Method:", 1, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell($valueWidth, 6, $shippingMethod, 1, 1, 'L');
        
        $this->Ln(5);
    }

    function InvoiceTable($items) {
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 10);
        
        $serviceWidth = 50;
        $quantityWidth = 25;
        $unitPriceWidth = 30;
        $descriptionWidth = 55;
        $amountWidth = 30;
        
        $this->Cell($serviceWidth, 6, 'Service', 1, 0, 'C', true);
        $this->Cell($quantityWidth, 6, 'Quantity', 1, 0, 'C', true);
        $this->Cell($unitPriceWidth, 6, 'Unit Price', 1, 0, 'C', true);
        $this->Cell($descriptionWidth, 6, 'Description', 1, 0, 'C', true);
        $this->Cell($amountWidth, 6, 'Amount', 1, 1, 'C', true);

        $this->SetFont('Arial', '', 9);
        foreach ($items as $item) {
            $this->Cell($serviceWidth, 6, $item['service'], 1);
            $this->Cell($quantityWidth, 6, $item['quantity'], 1, 0, 'C');
            $this->Cell($unitPriceWidth, 6, '$' . number_format($item['unit_price'], 2), 1, 0, 'C');
            $this->Cell($descriptionWidth, 6, $item['description'], 1);
            $this->Cell($amountWidth, 6, '$' . number_format($item['amount'], 2), 1, 1, 'R');
        }
    }

    function InvoiceTotal($subtotal, $tax = 0, $discountPercentage = 0, $totalPaid = 0) {
        $this->Ln(5);
        $this->SetFont('Arial', '', 8);
        
        $labelWidth = 160;
        $amountWidth = 30;
        
        $this->Cell($labelWidth, 6, 'Bill Amount', 0, 0, 'R');
        $this->Cell($amountWidth, 6, '$' . number_format($subtotal, 2), 0, 1, 'R');

        $discountAmount = ($subtotal * $discountPercentage) / 100;
        $this->Cell($labelWidth, 6, 'Discount (' . number_format($discountPercentage, 2) . '%)', 0, 0, 'R');
        $this->Cell($amountWidth, 6, '$' . number_format($discountAmount, 2), 0, 1, 'R');

        $netAmount = $subtotal - $discountAmount;
        $this->Cell($labelWidth, 6, 'Net Amount', 0, 0, 'R');
        $this->Cell($amountWidth, 6, '$' . number_format($netAmount, 2), 0, 1, 'R');

        $this->Cell($labelWidth, 6, 'Sales Tax', 0, 0, 'R');
        $this->Cell($amountWidth, 6, '$' . number_format($tax, 2), 0, 1, 'R');

        $total = $netAmount + $tax;
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($labelWidth, 6, 'Total Invoice Amount', 'T', 0, 'R');
        $this->Cell($amountWidth, 6, '$' . number_format($total, 2), 'T', 1, 'R');

        $this->SetFont('Arial', 'B', 8);
        $this->Cell($labelWidth, 6, 'Amount Paid', 0, 0, 'R');
        $this->Cell($amountWidth, 6, '$' . number_format($totalPaid, 2), 0, 1, 'R');

        $balanceDue = $total - $totalPaid;
        $this->Cell($labelWidth, 6, 'Balance Due', 'T', 0, 'R');
        $this->Cell($amountWidth, 6, '$' . number_format($balanceDue, 2), 'T', 1, 'R');
    }
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Process search and handle PDF generation/retrieval
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_type = sanitize_input($_POST['search_type']);
    $search_value = sanitize_input($_POST['search_value']);
    
    try {
        if ($search_type === 'invoice_number') {
            $query = "SELECT InvoiceId, InvoiceNumber FROM clientinvoice WHERE InvoiceNumber = ?";
        } else {
            $query = "SELECT InvoiceId, InvoiceNumber FROM clientinvoice WHERE InvoiceId = ?";
        }
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $search_value);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $invoiceId = $row['InvoiceId'];
            $invoiceNumber = $row['InvoiceNumber'];
            
            // Check if PDF already exists
            $pdfFileName = PDF_STORAGE_DIR . "Invoice_" . $invoiceNumber . ".pdf";
            
            if (file_exists($pdfFileName)) {
                // If PDF exists, serve it directly
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($pdfFileName) . '"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                ob_clean();
                readfile($pdfFileName);
                exit;
            }
            
            // Fetch invoice and client details
            $invoiceQuery = "
                SELECT i.InvoiceNumber, i.InvoiceDate, i.DueDate, c.ClientName, c.EIN, c.EmailId AS Email, 
                    c.Phone AS Voice, c.Addr1, c.City, c.State, c.Zip, i.BillAmount, i.Discounts, 
                    i.DiscountsDescriptions, i.Status, i.TotalPaid
                FROM clientinvoice i
                JOIN clients c ON i.ClientId = c.ClientId
                WHERE i.InvoiceId = ?";
            
            $stmt = $conn->prepare($invoiceQuery);
            $stmt->bind_param("i", $invoiceId);
            $stmt->execute();
            $invoiceData = $stmt->get_result()->fetch_assoc();
            
            if (!$invoiceData) {
                throw new Exception("Invoice data not found.");
            }
            
            // Initialize variables from invoice data
            $customerName = $invoiceData['ClientName'] ?? 'Unknown';
            $ein = $invoiceData['EIN'] ?? 'N/A';
            $email = $invoiceData['Email'] ?? 'N/A';
            $voice = $invoiceData['Voice'] ?? 'N/A';
            $discountPercentage = $invoiceData['Discounts'] ?? 0;
            $totalPaid = $invoiceData['TotalPaid'] ?? 0;
            $tax = 0; // Set tax as needed
            
            // Fetch invoice details
            $serviceQuery = "
                SELECT e.EmployeeName AS service, 
                    cid.Hours AS quantity, 
                    ces.BillingRate AS unit_price, 
                    ces.DueDays AS payment_terms,  
                    cid.TotalAmount AS amount,
                    i.DiscountsDescriptions AS description
                FROM clientinvoicedetails cid
                JOIN clientsemployeestate ces ON cid.ClientEmployeeStateId = ces.ClientEmployeeStateId
                JOIN employee e ON ces.EmployeeStateId = e.EmployeeStateId
                JOIN clientinvoice i ON cid.InvoiceId = i.InvoiceId
                WHERE cid.InvoiceId = ?";
            
            $stmt = $conn->prepare($serviceQuery);
            $stmt->bind_param("i", $invoiceId);
            $stmt->execute();
            $serviceResult = $stmt->get_result();
            
            $items = [];
            $subtotal = 0;
            while ($serviceRow = $serviceResult->fetch_assoc()) {
                $items[] = [
                    'service' => $serviceRow['service'],
                    'quantity' => $serviceRow['quantity'],
                    'unit_price' => $serviceRow['unit_price'],
                    'description' => $serviceRow['description'] ?? '', 
                    'amount' => $serviceRow['amount'],
                    'payment_terms' => $serviceRow['payment_terms']
                ];
                $subtotal += $serviceRow['amount'];
            }

            $paymentTerms = isset($items[0]['payment_terms']) 
                ? $items[0]['payment_terms'] . ' Days' 
                : 'Net 30 Days';

            $billTo = "{$invoiceData['ClientName']}\n{$invoiceData['Addr1']}\n{$invoiceData['City']}, {$invoiceData['State']} {$invoiceData['Zip']}";
            $shipTo = $billTo;

            // Generate PDF
            ob_clean();
            $pdf = new PDF_Invoice();
            $pdf->customerName = $customerName;
            $pdf->ein = $ein;
            $pdf->email = $email;
            $pdf->voice = $voice;
            $pdf->AddPage();

            $pdf->InvoiceDetails($invoiceData['InvoiceNumber'], $invoiceData['InvoiceDate']);
            $pdf->CompanyDetails($billTo, $shipTo);
            $pdf->AdditionalDetails(
                $paymentTerms, 
                $invoiceData['InvoiceDate'], 
                $invoiceData['DueDate'], 
                "Email"
            );
            $pdf->InvoiceTable($items);
            $pdf->InvoiceTotal($subtotal, $tax, $discountPercentage, $totalPaid);

            // Save and serve the PDF
            $pdf->Output('F', $pdfFileName);
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($pdfFileName) . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            ob_clean();
            readfile($pdfFileName);
            exit;
            
        } else {
            echo "Invoice not found for the provided search criteria.";
        }
    } catch (Exception $e) {
        echo "Error processing invoice: " . $e->getMessage();
    }
}
?>