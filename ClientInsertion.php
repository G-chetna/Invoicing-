<?php
// Turn off error display for production
error_reporting(0);
ini_set('display_errors', 0);

include 'Connection.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'error' => '',
    'clientExists' => false,
    'clientInfo' => null
];

// Handle GET requests (for fetching data)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    if ($_GET['action'] === 'getCorporates') {
        $corporateQuery = "SELECT CorporateId, CorporateName FROM corporates";
        $result = mysqli_query($conn, $corporateQuery);
        
        $corporates = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $corporates[] = $row;
        }
        
        $response['corporates'] = $corporates;
        $response['success'] = true;
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } 
    elseif ($_GET['action'] === 'getClientData' && isset($_GET['clientId'])) {
        $clientId = mysqli_real_escape_string($conn, $_GET['clientId']);
        
        $clientQuery = "SELECT c.*, cc.CorporateId, cc.Billingfrequency, cc.Billingperiod 
                       FROM clients c 
                       LEFT JOIN clientscorporates cc ON c.ClientId = cc.ClientId 
                       WHERE c.ClientId = '$clientId'";
        
        $result = mysqli_query($conn, $clientQuery);
        $clientData = mysqli_fetch_assoc($result);
        
        if ($clientData) {
            $response['success'] = true;
            $response['clientData'] = $clientData;
        } else {
            $response['error'] = "Client not found";
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Handle POST requests (for updates and inserts)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if we're in update mode
    $updateMode = isset($_POST['updateMode']) && $_POST['updateMode'] === 'true';
    $clientId = isset($_POST['clientId']) ? mysqli_real_escape_string($conn, $_POST['clientId']) : null;
    
    // Sanitize all inputs
    $clientName = mysqli_real_escape_string($conn, $_POST['clientName'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');
    $addr1 = mysqli_real_escape_string($conn, $_POST['address1'] ?? '');
    $addr2 = mysqli_real_escape_string($conn, $_POST['address2'] ?? '');
    $addr3 = mysqli_real_escape_string($conn, $_POST['address3'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    $state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
    $zip = mysqli_real_escape_string($conn, $_POST['zip'] ?? '');
    $ein = mysqli_real_escape_string($conn, $_POST['ein'] ?? '');
    $corporateId = mysqli_real_escape_string($conn, $_POST['corporateid'] ?? '');
    $billingfrequency = mysqli_real_escape_string($conn, $_POST['billingfrequency'] ?? '');
    $billingperiod = mysqli_real_escape_string($conn, $_POST['billingperiod'] ?? '');
    
    // Convert billing frequency to code
    function getBillingFrequencyCode($frequency) {
        $mapping = [
            'Full Month' => 1,
            'Half Month' => 2,
            'One Week' => 3,
            'Two Weeks' => 4,
            'Custom' => 5
        ];
        return $mapping[$frequency] ?? null;
    }
    
    $billingFrequencyCode = getBillingFrequencyCode($billingfrequency);
    
    // Server-side validation
    $errors = [];
    
    if (empty($clientName)) $errors[] = "Client name is required";
    if (empty($phone) || !preg_match('/^\d{10}$/', $phone)) $errors[] = "Valid phone number is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($country)) $errors[] = "Country is required";
    if (empty($addr1)) $errors[] = "Address line 1 is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($state)) $errors[] = "State is required";
    if (empty($zip) || !preg_match('/^\d{6}$/', $zip)) $errors[] = "Valid zip code is required";
    if (empty($ein)) $errors[] = "EIN is required";
    if (empty($corporateId)) $errors[] = "Corporate is required";
    if (empty($billingfrequency)) $errors[] = "Billing frequency is required";
    if ($billingfrequency === 'Custom' && empty($billingperiod)) {
        $errors[] = "Billing period is required for custom frequency";
    }
    
    if (!empty($errors)) {
        $response['error'] = implode(", ", $errors);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        if ($updateMode && $clientId) {
            // Update existing client
            $updateClientSql = "UPDATE clients SET 
                ClientName = ?,
                Phone = ?,
                EmailId = ?,
                Country = ?,
                Addr1 = ?,
                Addr2 = ?,
                Addr3 = ?,
                City = ?,
                State = ?,
                Zip = ?,
                EIN = ?
                WHERE ClientId = ?";
            
            $stmt = mysqli_prepare($conn, $updateClientSql);
            mysqli_stmt_bind_param($stmt, "sssssssssssi", 
                $clientName, $phone, $email, $country,
                $addr1, $addr2, $addr3, $city, $state,
                $zip, $ein, $clientId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating client: " . mysqli_stmt_error($stmt));
            }
            
            // Check if client-corporate relationship exists
            $checkRelationSql = "SELECT * FROM clientscorporates WHERE ClientId = ?";
            $stmt = mysqli_prepare($conn, $checkRelationSql);
            mysqli_stmt_bind_param($stmt, "i", $clientId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                // Update existing relationship
                $updateRelationSql = "UPDATE clientscorporates SET 
                    CorporateId = ?,
                    Billingfrequency = ?,
                    BillingFrequencyCode = ?,
                    Billingperiod = ?
                    WHERE ClientId = ?";
                
                $stmt = mysqli_prepare($conn, $updateRelationSql);
                mysqli_stmt_bind_param($stmt, "isisi", 
                    $corporateId, $billingfrequency, 
                    $billingFrequencyCode, $billingperiod, $clientId);
            } else {
                // Insert new relationship
                $insertRelationSql = "INSERT INTO clientscorporates 
                    (ClientId, CorporateId, Billingfrequency, BillingFrequencyCode, Billingperiod) 
                    VALUES (?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $insertRelationSql);
                mysqli_stmt_bind_param($stmt, "iisis", 
                    $clientId, $corporateId, $billingfrequency, 
                    $billingFrequencyCode, $billingperiod);
            }
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating client-corporate relationship: " . mysqli_stmt_error($stmt));
            }
            
            $response['success'] = true;
            $response['message'] = "Client updated successfully";
        } else {
            // Check if client with this EIN already exists
            $checkEinSql = "SELECT ClientId, ClientName, EIN, EmailId FROM clients WHERE EIN = ?";
            $stmt = mysqli_prepare($conn, $checkEinSql);
            mysqli_stmt_bind_param($stmt, "s", $ein);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                // Client exists
                $response['clientExists'] = true;
                $response['clientInfo'] = mysqli_fetch_assoc($result);
            } else {
                // Insert new client
                $insertClientSql = "INSERT INTO clients 
                    (ClientName, Phone, EmailId, Country, Addr1, Addr2, Addr3, City, State, Zip, EIN) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $insertClientSql);
                mysqli_stmt_bind_param($stmt, "sssssssssss", 
                    $clientName, $phone, $email, $country,
                    $addr1, $addr2, $addr3, $city, $state,
                    $zip, $ein);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error inserting client: " . mysqli_stmt_error($stmt));
                }
                
                $newClientId = mysqli_insert_id($conn);
                
                // Insert client-corporate relationship
                $insertRelationSql = "INSERT INTO clientscorporates 
                    (ClientId, CorporateId, Billingfrequency, BillingFrequencyCode, Billingperiod) 
                    VALUES (?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $insertRelationSql);
                mysqli_stmt_bind_param($stmt, "iisis", 
                    $newClientId, $corporateId, $billingfrequency, 
                    $billingFrequencyCode, $billingperiod);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error creating client-corporate relationship: " . mysqli_stmt_error($stmt));
                }
                
                $response['success'] = true;
                $response['message'] = "Client added successfully";
            }
        }
        
        // Commit transaction if all queries succeeded
        mysqli_commit($conn);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $response['error'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>