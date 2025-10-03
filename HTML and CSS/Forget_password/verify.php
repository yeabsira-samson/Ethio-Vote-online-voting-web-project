<?php
header('Content-Type: application/json');
include '../database.php'; 

$response = ["status" => "error", "message" => "Unknown error"];

try {
    $otp1 = $_POST['otp1'] ?? '';
    $otp2 = $_POST['otp2'] ?? '';
    $otp3 = $_POST['otp3'] ?? '';
    $otp4 = $_POST['otp4'] ?? '';
    $otp = $otp1 . $otp2 . $otp3 . $otp4;

    error_log("=== OTP VERIFICATION START ===");
    error_log("Received OTP: '$otp'");

    if (strlen($otp) !== 4 || !ctype_digit($otp)) {
        echo json_encode(["status" => "failure", "message" => "Invalid OTP format"]);
        exit;
    }

    $otp_escaped = $conn->real_escape_string($otp);

    
    error_log("--- PRE-VALIDATION CHECK ---");
    $pre_check_sql = "SELECT COUNT(*) as otp_count 
                      FROM OTP_verification 
                      WHERE otp_code = '$otp_escaped' 
                        AND status = 'not expired' 
                        AND expire_date_and_time > NOW()";
    
    $pre_check_result = $conn->query($pre_check_sql);
    $pre_check_row = $pre_check_result->fetch_assoc();
    $valid_otp_count = $pre_check_row['otp_count'];
    
    error_log("Pre-validation found $valid_otp_count valid OTP records");

    
    if ($valid_otp_count == 0) {
        error_log("PRE-VALIDATION FAILED: No valid OTP found - rejecting immediately");
        $response = ["status" => "failure", "message" => "OTP is invalid or expired"];
        echo json_encode($response);
        exit;
    }

    error_log("PRE-VALIDATION PASSED: Proceeding with stored procedure");

    
    error_log("--- CALLING STORED PROCEDURE ---");
    if (!$conn->query("CALL Verify_otp('$otp_escaped', @otp_status)")) {
        throw new Exception("Procedure call failed: " . $conn->error);
    }

    
    while ($conn->more_results()) {
        $conn->next_result();
        if ($result = $conn->store_result()) {
            $result->free();
        }
    }

    
    $result = $conn->query("SELECT @otp_status AS otp_status");
    if ($result && $row = $result->fetch_assoc()) {
        $message = $row['otp_status'];
        error_log("STORED PROCEDURE RETURNED: '$message'");
    } else {
        $message = '';
    }

    
    if ($valid_otp_count > 0) {
        $response = ["status" => "success", "message" => "Verified"];
        error_log("FINAL: VERIFIED (Based on pre-validation)");
    
    } else {
        $response = ["status" => "failure", "message" => "OTP is invalid or expired"];
        error_log("FINAL: REJECTED (Based on pre-validation)");
    }

    $conn->close();

} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    $response = ["status" => "error", "message" => $e->getMessage()];
}

error_log("=== OTP VERIFICATION END ===");
echo json_encode($response);
exit;
?>