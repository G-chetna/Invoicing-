<?php
header('Content-Type: application/json');

include 'Connection.php';

if (isset($_GET['action']) && $_GET['action'] === 'getCorporates') {
    $query = "SELECT CorporateId, CorporateName FROM corporates";
    $result = $conn->query($query);

    if ($result) {
        if ($result->num_rows > 0) {
            $corporates = [];
            while ($row = $result->fetch_assoc()) {
                $corporates[] = $row;
            }
            echo json_encode(['success' => true, 'corporates' => $corporates]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No corporates found.']);
        }
    } else {
        // Log the database error
        error_log("Database error: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
}

// Fetch clients based on corporateId
if (isset($_GET['action']) && $_GET['action'] === 'getClients' && isset($_GET['corporateId'])) {
    $corporateId = $conn->real_escape_string($_GET['corporateId']);

    $query = "SELECT ClientId, ClientName FROM clients WHERE ClientId IN (SELECT ClientId FROM clientscorporates WHERE CorporateId = ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('i', $corporateId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $clients = [];
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
            echo json_encode(['success' => true, 'clients' => $clients]);
        } else {
            error_log("Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Error fetching clients.']);
        }
        $stmt->close();
    } else {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
}


// Fetch billing frequency and last invoice date
if (isset($_GET['action']) && $_GET['action'] === 'getClientDetails' && isset($_GET['clientId'])) {
    $clientId = $conn->real_escape_string($_GET['clientId']);

    // Fetch billing frequency from clientscorporates table
    $query = "SELECT BillingFrequencyCode FROM clientscorporates WHERE ClientId = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit;
    }

    $stmt->bind_param('i', $clientId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $billingFrequency = $row['BillingFrequencyCode'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Billing frequency not found.']);
            exit;
        }
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error fetching billing frequency.']);
        exit;
    }
    $stmt->close();

    // Fetch last invoice date from clientinvoice table
    $query = "SELECT MAX(InvoiceDate) AS LastInvoiceDate FROM clientinvoice WHERE ClientId = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit;
    }

    $stmt->bind_param('i', $clientId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $lastInvoiceDate = $row['LastInvoiceDate'];
        } else {
            $lastInvoiceDate = null; // No previous invoice found
        }
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error fetching last invoice date.']);
        exit;
    }
    $stmt->close();

    // Return billing frequency and last invoice date
    echo json_encode([
        'success' => true,
        'billingFrequency' => $billingFrequency,
        'lastInvoiceDate' => $lastInvoiceDate
    ]);
    exit;
}

// Fetch employees and billing rates in one query
if (isset($_GET['clientId'])) {
    $clientId = $conn->real_escape_string($_GET['clientId']);

    $query = "SELECT 
                ces.ClientEmployeeStateId,
                e.EmployeeName,
                ces.BillingRate
              FROM clientsemployeestate ces
              JOIN employee e ON e.EmployeeStateId = ces.EmployeeStateID
              WHERE ces.ClientId = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit;
    }

    $stmt->bind_param('i', $clientId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = [
                'ClientEmployeeStateId' => $row['ClientEmployeeStateId'],
                'EmployeeName' => $row['EmployeeName'],
                'BillingRate' => $row['BillingRate']
            ];
        }
        echo json_encode(['success' => true, 'data' => $employees]);
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error fetching employees.']);
    }
    $stmt->close();
    exit;
}

// Check if invoice number exists
if (isset($_GET['action']) && $_GET['action'] === 'checkInvoiceNumber' && isset($_GET['invoiceNumber'])) {
    $invoiceNumber = $conn->real_escape_string($_GET['invoiceNumber']);

    $query = "SELECT COUNT(*) as count FROM clientinvoice WHERE InvoiceNumber = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $stmt->bind_param('s', $invoiceNumber);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        echo json_encode([
            'success' => true,
            'exists' => $row['count'] > 0
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to check invoice number']);
    }

    $stmt->close();
    exit;
}

// Handle form submission with invoice number
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoiceNumber = isset($_POST['invoiceNumber']) ? $_POST['invoiceNumber'] : '';

    // Validate invoice number format
    if (!preg_match('/^\d{16}$/', $invoiceNumber)) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice number format']);
        exit;
    }

    // Check if invoice number exists
    $query = "SELECT COUNT(*) as count FROM clientinvoice WHERE InvoiceNumber = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $invoiceNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Invoice number already exists']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Invoice created successfully']);
    exit;
}

$conn->close();
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>