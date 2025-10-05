<?php
session_start();
header('Content-Type: application/json');
include '../database.php';

$password = $_POST['password'] ?? '';
$email = $_SESSION['Rest_user_email'] ?? ''; 

if (empty($password) || empty($email)) {
    echo json_encode(["status" => "error", "message" => "Password or email missing"]);
    exit;
}

// ADDED: Password validation - must contain at least one letter and one number
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    echo json_encode(["status" => "error", "message" => "Password must contain both letters and numbers"]);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long"]);
    exit;
}

$hashed_pass = password_hash($password, PASSWORD_BCRYPT);

$sql = "CALL Update_password(?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $hashed_pass);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode(["status" => "success", "message" => $row['Message']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Unexpected response"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update"]);
}
?>