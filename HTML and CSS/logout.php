<?php
session_start();
header('Content-Type: application/json');

include 'database.php';

$response = [
    'success' => false,
    'message' => 'Unknown error',
    'redirect' => 'http://localhost:8000/Home/home.html' 
];

if (isset($_SESSION['voter_id'])) {
    $voterId = $_SESSION['voter_id'];

    
    if (isset($conn)) {
        $stmt = $conn->prepare("CALL Logout_status(?)");
        if ($stmt) {
            $stmt->bind_param("s", $voterId);
            $stmt->execute();
            $stmt->close();
            while ($conn->more_results() && $conn->next_result()) {}
        }
    }

    session_unset();
    session_destroy();

    $response['success'] = true;
    $response['message'] = 'Logged out successfully';
} else {
    $response['success'] = false;
    $response['message'] = 'You are already logged out';
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
