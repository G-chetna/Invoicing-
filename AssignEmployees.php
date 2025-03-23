<?php
include 'Connection.php';

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function fetchData($conn, $query, $paramType = null, $paramValue = null) {
    if ($paramType && $paramValue) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($paramType, $paramValue);
    } else {
        $stmt = $conn->prepare($query);
    }

    if ($stmt) {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                array_walk_recursive($row, function(&$item) {
                    $item = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                });
                $data[] = $row;
            }
            $stmt->close();
            return $data;
        } else {
            error_log("Query execution failed: " . $stmt->error);
            return ["error" => "Database error occurred"];
        }
    }
    error_log("Query preparation failed: " . $conn->error);
    return ["error" => "Database error occurred"];
}

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'getCorporates') {
        // Fetch all corporates
        $corporateQuery = "SELECT CorporateId, CorporateName FROM corporates";
        $response['corporates'] = fetchData($conn, $corporateQuery);
    }
    elseif (isset($_GET['corporateId']) && ctype_digit($_GET['corporateId'])) {
        $corporateId = (int)$_GET['corporateId'];

        $clientQuery = "SELECT c.ClientId, c.ClientName 
                       FROM clients c
                       INNER JOIN clientscorporates cc ON c.ClientId = cc.ClientId
                       WHERE cc.CorporateId = ?";
        
        $employeeQuery = "SELECT EmployeeStateId, EmployeeName 
                         FROM employee 
                         WHERE CorporateID = ?";

        $clients = fetchData($conn, $clientQuery, 'i', $corporateId);
        $employees = fetchData($conn, $employeeQuery, 'i', $corporateId);

        $response = [
            'clients' => $clients,
            'employees' => $employees
        ];
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requiredFields = ['clientId', 'employeeStateId', 'startDate', 'endDate', 'billingRate', 'duedays'];
    $missingFields = array_filter($requiredFields, function($field) {
        return !isset($_POST[$field]) || trim($_POST[$field]) === '';
    });

    if (empty($missingFields)) {
        $clientId = filter_var($_POST['clientId'], FILTER_VALIDATE_INT);
        $employeeStateId = filter_var($_POST['employeeStateId'], FILTER_VALIDATE_INT);
        $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
        $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
        $billingRate = filter_var($_POST['billingRate'], FILTER_VALIDATE_FLOAT);
        $duedays = filter_var($_POST['duedays'], FILTER_VALIDATE_INT);

        if ($clientId && $employeeStateId && validateDate($startDate) && validateDate($endDate) && 
            $billingRate > 0 && $duedays > 0) {

            $checkQuery = "SELECT COUNT(*) as count FROM clientsemployeestate 
                          WHERE ClientId = ? AND EmployeeStateId = ? AND 
                          ((StartDate BETWEEN ? AND ?) OR (EndDate BETWEEN ? AND ?))";
            
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('iissss', $clientId, $employeeStateId, $startDate, $endDate, $startDate, $endDate);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();

            if ($checkResult['count'] == 0) {
                $insertQuery = "INSERT INTO clientsemployeestate 
                              (ClientId, EmployeeStateId, StartDate, EndDate, BillingRate, DueDays) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param('iissdi', $clientId, $employeeStateId, $startDate, $endDate, $billingRate, $duedays);

                if ($insertStmt->execute()) {
                    $response = ["message" => "Employee successfully assigned to client."];
                } else {
                    $response = ["error" => "Failed to insert assignment"];
                    error_log("Insert failed: " . $insertStmt->error);
                }
                $insertStmt->close();
            } else {
                $response = ["error" => "Employee already assigned to this client for the specified date range"];
            }
            $checkStmt->close();
        } else {
            $response = ["error" => "Invalid input data"];
        }
    } else {
        $response = ["error" => "Missing required fields: " . implode(', ', $missingFields)];
    }
}

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>