<?php
session_start();
include '../database.php'; 

ini_set('display_errors', 0); 
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

ob_start();

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fin = trim($_POST['fin'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($fin === '' || $password === '' || $confirm_password === '') {
        $response['message'] = 'Please fill in all fields.';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }

    if ($password !== $confirm_password) {
        $response['message'] = 'Password and Confirm Password do not match.';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password)) {
        $response['message'] = 'Password must be at least 6 characters long and contain at least one letter and one number.';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }

    if (strlen($fin) > 20) {
        $response['message'] = 'FIN is too long. Maximum length is 20 characters.';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }

    if (!preg_match('/^[A-Za-z0-9]+$/', $fin)) {
        $response['message'] = 'FIN must contain only letters and numbers.';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    if (strlen($hashedPassword) > 255) {
        $response['message'] = 'Generated password hash is too long for storage.';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }


    if ($stmt = $conn->prepare("CALL Registration_submit(?, ?)")) {
        $stmt->bind_param("ss", $fin, $hashedPassword);
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $response['message'] = $row['Message'] ?? 'No message returned';
                $response['success'] = stripos($row['Message'], 'Inserted Successfully') !== false;
                if ($response['success']) {
                    $_SESSION['voter_id'] = substr($row['Message'], strpos($row['Message'], 'VR'));
                }
            } else {
                $response['message'] = 'No result returned from procedure.';
            }
        } catch (mysqli_sql_exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) {;}
    } else {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
    }
}

echo json_encode($response);
ob_end_flush();
exit;
?>