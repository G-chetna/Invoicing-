<?php
include 'Connection.php';

// Function to fetch data
function fetchData($conn, $query) {
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'error' => ''
);

// Handle GET request for fetching corporates
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    if ($_GET['action'] === 'getCorporates') {
        $corporateQuery = "SELECT CorporateId, CorporateName FROM corporates";
        $response['corporates'] = fetchData($conn, $corporateQuery);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $clientName = mysqli_real_escape_string($conn, $_POST['clientName']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $addr1 = mysqli_real_escape_string($conn, $_POST['address1']);
    $addr2 = mysqli_real_escape_string($conn, $_POST['address2']);
    $addr3 = mysqli_real_escape_string($conn, $_POST['address3']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $zip = mysqli_real_escape_string($conn, $_POST['zip']);
    $ein = mysqli_real_escape_string($conn, $_POST['ein']);
    $corporateId = mysqli_real_escape_string($conn, $_POST['corporateid']);
    $billingfrequency = mysqli_real_escape_string($conn, $_POST['billingfrequency']);
    $billingperiod = mysqli_real_escape_string($conn, $_POST['billingperiod']);
    function getBillingFrequencyCode($billingFrequency) {
        switch ($billingFrequency) {
            case 'Full Month':
                return 1;
            case 'Half Month':
                return 2;
            case 'One Week':
                return 3;
            case 'Two Weeks':
                return 4;
            case 'Custom':
                return 5;
            default:
                return null;
        }
    }
    
    // Get billing frequency code
    $billingFrequencyCode = getBillingFrequencyCode($billingFrequency);
    // Server-side validation
    if (empty($clientName) || empty($phone) || empty($email) || empty($country) || 
        empty($addr1) || empty($city) || empty($state) || empty($zip) || empty($ein) || 
        empty($corporateId) || empty($billingfrequency)) {
        $response['error'] = "All required fields must be filled out";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Invalid email format";
    } elseif (!is_numeric($phone)) {
        $response['error'] = "Phone number must contain only numbers";
    } else {
        // Check if EIN already exists
        $checkEINSql = "SELECT ClientID FROM clients WHERE EIN = ?";
        $stmt = mysqli_prepare($conn, $checkEINSql);
        mysqli_stmt_bind_param($stmt, "s", $ein);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $existingClient = mysqli_fetch_assoc($result);
            $response['success'] = false;
            $response['error'] = "This client already exists with ClientID: " . $existingClient['ClientID'];
        } else {
            // Proceed with the insertion
            mysqli_begin_transaction($conn);
            try {
                // Insert into clients table
                $clientSql = "INSERT INTO clients (ClientName, Phone, EmailId, Country, Addr1, Addr2, Addr3, City, State, Zip, EIN) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $clientSql);
                mysqli_stmt_bind_param($stmt, "sssssssssss", $clientName, $phone, $email, $country, $addr1, $addr2, $addr3, $city, $state, $zip, $ein);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error inserting client data: " . mysqli_stmt_error($stmt));
                }

                $clientId = mysqli_insert_id($conn);

                // Insert into clientscorporates table
                $corporateSql = "INSERT INTO clientscorporates (ClientId, CorporateId, DateAdded, Billingperiod, Billingfrequency,BillingFrequencyCode) 
                                 VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?,?)";
                $stmt = mysqli_prepare($conn, $corporateSql);
                mysqli_stmt_bind_param($stmt, "iiis", $clientId, $corporateId, $billingperiod, $billingfrequency,$billingFrequencyCode);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error creating client-corporate association: " . mysqli_stmt_error($stmt));
                }

                // Commit transaction
                mysqli_commit($conn);
                $response['success'] = true;
                $response['message'] = "Client added successfully";

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $response['error'] = $e->getMessage();
            }
        }
    }
}

// Close connection
mysqli_close($conn);
if ($_GET['action'] === 'checkInvoiceNumber') {
    $invoiceNumber = $_GET['invoiceNumber'];

    // Validate the invoice number
    if (strlen($invoiceNumber) !== 16 || !ctype_digit($invoiceNumber)) {
        echo json_encode(['exists' => false, 'message' => 'Invalid invoice number format.']);
        exit;
    }

    // Check if the invoice number exists in the database
    $query = "SELECT COUNT(*) AS count FROM clientinvoice WHERE InvoiceNumber = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $invoiceNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode(['exists' => $row['count'] > 0]);
    exit;
}
// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>