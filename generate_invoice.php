<?php
include('Connection.php');

$invoiceId = null;
$pdfDirectory = 'InvoicesPDF';

// Create directory if it doesn't exist
if (!file_exists($pdfDirectory)) {
    mkdir($pdfDirectory, 0777, true);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    exit;
}
// Sanitize all POST inputs with default values if missing
$corporateId = $conn->real_escape_string($_POST['corporateid']);
$clientId = $conn->real_escape_string($_POST['clientId']);
$discountPercentage = $conn->real_escape_string($_POST['discountPercentage']);
$discountDescription = $conn->real_escape_string($_POST['discountDescription']);
$totalPaid = $conn->real_escape_string($_POST['totalPaid']);

// Ensure employee-related fields are arrays
$employeeIds = isset($_POST['employeeId']) && is_array($_POST['employeeId']) ? array_map(function($value) use ($conn) {
    return $conn->real_escape_string($value);
}, $_POST['employeeId']) : [];
$startDates = isset($_POST['startDate']) && is_array($_POST['startDate']) ? array_map(function($value) use ($conn) {
    return $conn->real_escape_string($value);
}, $_POST['startDate']) : [];
$endDates = isset($_POST['endDate']) && is_array($_POST['endDate']) ? array_map(function($value) use ($conn) {
    return $conn->real_escape_string($value);
}, $_POST['endDate']) : [];
$hours = isset($_POST['hours']) && is_array($_POST['hours']) ? array_map(function($value) use ($conn) {
    return $conn->real_escape_string($value);
}, $_POST['hours']) : [];
$totalAmounts = isset($_POST['totalAmount']) && is_array($_POST['totalAmount']) ? array_map(function($value) use ($conn) {
    return $conn->real_escape_string($value);
}, $_POST['totalAmount']) : [];

$discountAmount = $conn->real_escape_string($_POST['finalAmount'] ?? '0');
$billAmount = $conn->real_escape_string($_POST['totalBill'] ?? '0');

$invoiceDate = date('Y-m-d');
$paymentDate = $invoiceDate;
$dueDate = date('Y-m-d', strtotime('+14 days'));
$invoiceNumber = date('Ymd') . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

$totalPaid = number_format((float)$totalPaid, 2, '.', '');
$billAmount = number_format((float)$billAmount, 2, '.', '');

$status = ($totalPaid == $discountAmount) ? 'Closed' : (($totalPaid < $discountAmount) ? 'PartialPaid' : 'FullDue');

try {
    // Check if the invoice already exists
    $checkQuery = "SELECT InvoiceId FROM clientinvoice WHERE corporateId = ? AND ClientId = ? AND BillAmount = ? AND InvoiceDate = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("iids", $corporateId, $clientId, $billAmount, $invoiceDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Invoice exists, get the existing invoice ID
        $row = $result->fetch_assoc();
        $invoiceId = $row['InvoiceId'];
    } else {
        // Start transaction for new invoice
        $conn->begin_transaction();

        // Insert into clientinvoice table
        $insertInvoiceQuery = "
            INSERT INTO clientinvoice (corporateId, ClientId, InvoiceNumber, InvoiceDate, BillAmount, PaymentDate, DueDate, Discounts, DiscountsDescriptions, Discountedamount, Status, TotalPaid) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertInvoiceQuery);
        $stmt->bind_param(
            "iissdssssdsd",
            $corporateId,
            $clientId,
            $invoiceNumber,
            $invoiceDate,
            $billAmount,
            $paymentDate,
            $dueDate,
            $discountPercentage,
            $discountDescription,
            $discountAmount,
            $status,
            $totalPaid
        );
        $stmt->execute();
        $invoiceId = $stmt->insert_id;

        // Insert into clientinvoicedetails table
        foreach ($employeeIds as $index => $employeeId) {
            $startDate = $startDates[$index] ?? '';
            $endDate = $endDates[$index] ?? '';
            $hour = $hours[$index] ?? '0';
            $totalAmount = $totalAmounts[$index] ?? '0';

            $detailsQuery = "
                INSERT INTO clientinvoicedetails (InvoiceId, ClientEmployeeStateId, StartDate, EndDate, Hours, TotalAmount) 
                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($detailsQuery);
            $stmt->bind_param("issssd", $invoiceId, $employeeId, $startDate, $endDate, $hour, $totalAmount);
            $stmt->execute();
        }

        $conn->commit();
    }

    // Get the PDF filename based on invoice details
    $pdfFilename = 'invoice_' . $invoiceNumber . '.pdf';
    $pdfPath = $pdfDirectory . '/' . $pdfFilename;

    // Only generate PDF if it doesn't exist
    if (!file_exists($pdfPath)) {
        require('fpd/fpdf.php');
        class PDF_Invoice extends FPDF {
            public $customerName;
            public $ein;
            public $email;
            public $voice;
    
            function Header() {
                $this->SetTextColor(150, 150, 150);
                $this->SetFont('Arial', 'B', 25); 
            
                //  "INVOICE" text as a watermark at the top right
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
                $this->SetFont('Arial', 'B', 9);  // Bold for label
                $this->Cell(17, 6, 'Customer:', 0, 0, 'L');
                $this->SetFont('Arial', '', 8);   // Normal for value
                $this->Cell(0, 6, ($this->customerName ?? 'N/A'), 0, 1, 'L');
    
                // EIN
                $this->SetFont('Arial', 'B', 9);
                $this->Cell(15, 6, 'EIN:', 0, 0, 'L');
                $this->SetFont('Arial', '', 8);
                $this->Cell(0, 6, ($this->ein ?? 'N/A'), 0, 1, 'L');
    
                // Email
                $this->SetFont('Arial', 'B', 9);
                $this->Cell(15, 6, 'Email:', 0, 0, 'L');
                $this->SetFont('Arial', '', 8);
                $this->Cell(0, 6, ($this->email ?? 'N/A'), 0, 1, 'L');
    
                // Voice
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
                // Store the current position
                $x = $this->GetX();
                $y = $this->GetY();
                
                // Calculate height by counting the number of lines
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
                // Add the last line
                $lines++;
                
                // Add extra lines for manual line breaks
                $lines += substr_count($txt, "\n");
                
                // Restore the position
                $this->SetXY($x, $y);
                
                // Return total height
                return $lines * $h;
            }
        
            function CompanyDetails($billTo, $shipTo) {
                $startY = 65;
                $this->SetY($startY);
                $this->SetFillColor(230, 230, 230);
                $this->SetFont('Arial', 'B', 10);
                
                // Total width of 190 (standard A4 with margins)
                $width = 92.5; // (190 - 5) / 2 for equal columns with 5 spacing
                
                $this->Cell($width, 6, 'Bill To:', 1, 0, 'L', true);
                $this->Cell(5);
                $this->Cell($width, 6, 'Ship To:', 1, 1, 'L', true);
        
                $this->SetFont('Arial', '', 10);
                $startX = $this->GetX();
                
                // Calculate heights for both address blocks
                $billToHeight = $this->GetMultiCellHeight($width, 6, $billTo);
                $shipToHeight = $this->GetMultiCellHeight($width, 6, $shipTo);
                $maxHeight = max($billToHeight, $shipToHeight);
        
                //  rectangles for both address blocks
                $this->Rect($startX, $this->GetY(), $width, $maxHeight);
                $this->Rect($startX + $width + 5, $this->GetY(), $width, $maxHeight);
        
                // Print the addresses
                $this->MultiCell($width, 6, $billTo, 0, 'L');
                $this->SetXY($startX + $width + 5, $startY + 6);
                $this->MultiCell($width, 6, $shipTo, 0, 'L');
                
                // Set Y position for next element
                $this->SetY($startY + $maxHeight + 10);
            }
            function AdditionalDetails($paymentTerms, $shipDate, $dueDate, $shippingMethod) {
                
                $labelWidth = 45;
                $valueWidth = 50; // total width is 190
                
                // First row
                $this->SetFont('Arial', 'B', 10); // Bold for label
                $this->Cell($labelWidth, 6, "Payment Terms:", 1, 0, 'L');
                $this->SetFont('Arial', '', 10);  // Normal for value
                $this->Cell($valueWidth, 6, $paymentTerms, 1, 0, 'L');
                
                $this->SetFont('Arial', 'B', 10); // Bold for label
                $this->Cell($labelWidth, 6, "Ship Date:", 1, 0, 'L');
                $this->SetFont('Arial', '', 10);  // Normal for value
                $this->Cell($valueWidth, 6, $shipDate, 1, 1, 'L');
            
                // Second row
                $this->SetFont('Arial', 'B', 10); // Bold for label
                $this->Cell($labelWidth, 6, "Due Date:", 1, 0, 'L');
                $this->SetFont('Arial', '', 10);  // Normal for value
                $this->Cell($valueWidth, 6, $dueDate, 1, 0, 'L');
                
                $this->SetFont('Arial', 'B', 10); // Bold for label
                $this->Cell($labelWidth, 6, "Shipping Method:", 1, 0, 'L');
                $this->SetFont('Arial', '', 10);  // Normal for value
                $this->Cell($valueWidth, 6, $shippingMethod, 1, 1, 'L');
                
                $this->Ln(5);
            }
        
            function InvoiceTable($items) {
                $this->SetFillColor(230, 230, 230);
                $this->SetFont('Arial', 'B', 10);
                
                // Column widths  to total exactly 190
                $serviceWidth = 50;
                $quantityWidth = 25;
                $unitPriceWidth = 30;
                $descriptionWidth = 55;
                $amountWidth = 30;
                
                // Total width = 190 
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
                
                // Use the same total width as the invoice table (190)
                $labelWidth = 160;
                $amountWidth = 30;
                
                // Bill Amount (Subtotal)
                $this->Cell($labelWidth, 6, 'Bill Amount', 0, 0, 'R');
                $this->Cell($amountWidth, 6, '$' . number_format($subtotal, 2), 0, 1, 'R');
        
                // Discount Amount
                $discountAmount = ($subtotal * $discountPercentage) / 100;
                $this->Cell($labelWidth, 6, 'Discount (' . number_format($discountPercentage, 2) . '%)', 0, 0, 'R');
                $this->Cell($amountWidth, 6, '$' . number_format($discountAmount, 2), 0, 1, 'R');
        
                // Net Amount after discount
                $netAmount = $subtotal - $discountAmount;
                $this->Cell($labelWidth, 6, 'Net Amount', 0, 0, 'R');
                $this->Cell($amountWidth, 6, '$' . number_format($netAmount, 2), 0, 1, 'R');
        
                // Sales Tax
                $this->Cell($labelWidth, 6, 'Sales Tax', 0, 0, 'R');
                $this->Cell($amountWidth, 6, '$' . number_format($tax, 2), 0, 1, 'R');
        
                // Total Invoice Amount
                $total = $netAmount + $tax;
                $this->SetFont('Arial', 'B', 9);
                $this->Cell($labelWidth, 6, 'Total Invoice Amount', 'T', 0, 'R');
                $this->Cell($amountWidth, 6, '$' . number_format($total, 2), 'T', 1, 'R');
        
                // Balance Due
                $balanceDue = $total - $totalPaid;
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(160, 6, 'Balance Due', 'T', 0, 'R');
                $this->Cell(30, 6, '$' . number_format($balanceDue, 2), 'T', 1, 'R');
    
                // Amount Paid
                $this->SetFont('Arial', 'B', 9);
                $this->Cell($labelWidth, 6, 'Amount Paid', 0, 0, 'R');
                $this->Cell($amountWidth, 6, '$' . number_format($totalPaid, 2), 0, 1, 'R'); 
            }
        }

        // Fetching Client and Invoice Details
        $invoiceQuery = "
            SELECT i.InvoiceNumber, i.InvoiceDate, i.DueDate, c.ClientName, c.EIN, c.EmailId AS Email, c.Phone AS Voice, c.Addr1, c.City, c.State, c.Zip, 
                   i.BillAmount, i.Discounts, i.DiscountsDescriptions, i.Status, i.TotalPaid
            FROM clientinvoice i
            JOIN clients c ON i.ClientId = c.ClientId
            WHERE i.InvoiceId = ?";
        $stmt = $conn->prepare($invoiceQuery);
        $stmt->bind_param("i", $invoiceId);
        $stmt->execute();
        $invoiceData = $stmt->get_result()->fetch_assoc();

        if (!$invoiceData) {
            die("Invoice data not found.");
        }

        $customerName = $invoiceData['ClientName'] ?? 'Unknown';
        $ein = $invoiceData['EIN'] ?? 'N/A';
        $email = $invoiceData['Email'] ?? 'N/A';
        $voice = $invoiceData['Voice'] ?? 'N/A';
        $billTo = "{$invoiceData['ClientName']}\n{$invoiceData['Addr1']}\n{$invoiceData['City']}, {$invoiceData['State']} {$invoiceData['Zip']}";
        $shipTo = "{$invoiceData['ClientName']}\n{$invoiceData['Addr1']}\n{$invoiceData['City']}, {$invoiceData['State']} {$invoiceData['Zip']}";

        $items = [];
        $subtotal = 0;
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

        while ($row = $serviceResult->fetch_assoc()) {
            $row['description'] = $invoiceData['DiscountsDescriptions'] ?? 'No description';
            $items[] = $row;
            $subtotal += $row['amount'] ?? 0;
        }

        $pdf = new PDF_Invoice();
        $pdf->customerName = $customerName;
        $pdf->ein = $ein;
        $pdf->email = $email;
        $pdf->voice = $voice;
        $pdf->AddPage();
        $pdf->InvoiceDetails($invoiceData['InvoiceNumber'] ?? 'N/A', $invoiceData['InvoiceDate'] ?? 'N/A');
        $pdf->CompanyDetails($billTo, $shipTo);

        $paymentTerms = $items[0]['payment_terms'] . ' Days';
        $shipDate = $invoiceData['InvoiceDate'] ?? 'N/A';
        $dueDate = $invoiceData['DueDate'] ?? 'N/A';
        $shippingMethod = "Email";

        $pdf->AdditionalDetails($paymentTerms, $shipDate, $dueDate, $shippingMethod);
        $pdf->InvoiceTable($items);

        $discountPercentage = floatval($invoiceData['Discounts'] ?? 0);
        $totalPaid = floatval($invoiceData['TotalPaid'] ?? 0);
        $pdf->InvoiceTotal($subtotal, 0, $discountPercentage, $totalPaid);

        // Save PDF to file only
        $pdf->Output('F', $pdfPath);
    }

    // Redirect to the saved PDF file
    header("Location: $pdfDirectory/" . urlencode($pdfFilename));
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    if (!$result->num_rows > 0) {
        $conn->rollback();
    }
    echo "Error: " . $e->getMessage();
    exit;
}
?>