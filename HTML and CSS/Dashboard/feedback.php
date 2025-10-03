<?php
header('Content-Type: application/json');
ob_start();

include '../database.php'; 

$response = ["status" => "error", "message" => "Unknown error"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = $_POST['feedback'] ?? '';

    if (!empty($feedback)) {
        $stmt = $conn->prepare("INSERT INTO Feedback (feedback) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $feedback);
            if ($stmt->execute()) {
                $response = ["status" => "success", "message" => "Feedback submitted successfully!"];
            } else {
                $response = ["status" => "error", "message" => "Execute failed: " . $stmt->error];
            }
            $stmt->close();
        } else {
            $response = ["status" => "error", "message" => "Prepare failed: " . $conn->error];
        }
    } else {
        $response = ["status" => "error", "message" => "Feedback cannot be empty."];
    }

    $conn->close();
}

ob_end_clean();
echo json_encode($response);
exit;  