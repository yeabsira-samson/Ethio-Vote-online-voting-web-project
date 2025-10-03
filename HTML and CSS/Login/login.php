<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);

include '../database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

header('Content-Type: application/json');

session_start();

$response = ["success" => false, "message" => "Unknown error", "redirect" => ""];

try {
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $voterId = $_POST['voter_id'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!$voterId || !$password) {
            $response['message'] = "Please enter both Voter ID and Password!";
        } else {
        
            $stmt = $conn->prepare("SELECT voter_identification AS voter_id, password FROM voter WHERE voter_identification = ?");
            if ($stmt === false) {
                throw new Exception("Database error: Failed to prepare statement - " . $conn->error);
            }

            $stmt->bind_param("s", $voterId);
            if (!$stmt->execute()) {
                throw new Exception("Database error: Failed to execute statement - " . $stmt->error);
            }

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row && password_verify($password, $row['password'])) {
                $_SESSION['voter_id'] = $row['voter_id'];

            
                $updateStmt = $conn->prepare("UPDATE voter SET status = 'loggedin' WHERE voter_identification = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("s", $voterId);
                    $updateStmt->execute();
                    $updateStmt->close();
                }

                $response = [
                    "success" => true,
                    "message" => "Login successful!",
                    "redirect" => "http://localhost:8000/Dashboard/dashboard.html"
                ];
            } else {
                $response['message'] = "Invalid Voter ID or Password!";
                $response['redirect'] = "";
            }
        }
    }
} catch (Exception $e) {
    $response['message'] = "Server error: " . $e->getMessage();
    $response['redirect'] = "";
}

ob_end_clean();
echo json_encode($response);
exit;
?>
