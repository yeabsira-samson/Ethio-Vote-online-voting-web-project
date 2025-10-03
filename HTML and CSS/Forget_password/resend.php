<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

session_start();
header('Content-Type: application/json');

error_log("=== OTP EMAIL SENDING STARTED ===");

$response = ["status" => "error", "message" => "Unknown error"];

try {
    $email = $_SESSION['Rest_user_email'] ?? ''; 
    error_log("Session email: " . $email);
    
    if (empty($email)) {
        $response = ["status" => "error", "message" => "Email not found in session"];
        error_log("ERROR: No email in session");
        echo json_encode($response);
        exit;
    }

    
    try {
        include '../database.php';
        
        
        if (!isset($pdo)) {
            throw new Exception("PDO connection not established");
        }
        
        
        $pdo->query("SELECT 1");
        error_log("Database connection successful");
        
    } catch (Exception $dbError) {
        error_log("Database connection failed: " . $dbError->getMessage());
        
        
        error_log("Attempting direct database connection...");
        $pdo = new PDO("mysql:host=localhost;dbname=ethiovote", "root", "ZAde3518.");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        error_log("Direct database connection successful");
    }

    
    $pdo->exec("SET @otp = ''");
    $pdo->exec("SET @result = 0");

    $stmt = $pdo->prepare("CALL Check_Email_And_Generate_OTP(:email_input, @otp, @result)");
    $stmt->bindParam(':email_input', $email, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        throw new Exception("Stored procedure execution failed");
    }
    
    $stmt->closeCursor();

    $out = $pdo->query("SELECT @otp AS otp, @result AS result")->fetch(PDO::FETCH_ASSOC);
    
    if (!$out) {
        throw new Exception("Failed to get OTP result from procedure");
    }
    
    error_log("Stored procedure result: " . print_r($out, true));

    if ($out && $out['result'] == 1) {
        $otp = $out['otp']; 
        error_log("OTP generated: " . $otp);
        
        
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_time'] = time();
        
        
        $apiKey = "89BEEDCDB918A388BDDE702B1ABA8493F1199D291EEB4B30AE54AC9E37930661440C4F61025C1FF11F5B2D1A841960E2";
        
        $postData = [
            'Recipients' => [
                'To' => [$email]
            ],
            'Content' => [
                'Body' => [
                    [
                        'ContentType' => 'HTML',
                        'Charset' => 'utf-8',
                        'Content' => "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                                <h2 style='color: #333;'>Ethiopian Voting System</h2>
                                <h1 style='color: #007bff;'>Your OTP Code</h1>
                                <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; text-align: center;'>
                                    <p style='font-size: 18px; margin: 0;'>Your One-Time Password is:</p>
                                    <h1 style='font-size: 36px; color: #007bff; margin: 10px 0;'>{$otp}</h1>
                                </div>
                                <p style='color: #666;'>This OTP will expire in 1 minute.</p>
                                <p style='color: #666; font-size: 12px;'>If you didn't request this code, please ignore this email.</p>
                            </div>
                        "
                    ]
                ],
                'From' => 'heven3518@gmail.com',
                'FromName' => 'Ethiopian Voting System',
                'Subject' => 'Your OTP Code - Ethiopian Voting System'
            ]
        ];

        error_log("Attempting to send email with Elastic Email v4 to: " . $email);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.elasticemail.com/v4/emails/transactional",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-ElasticEmail-ApiKey: ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $emailResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        error_log("Elastic Email v4 - HTTP Code: " . $httpCode);
        error_log("Elastic Email v4 - Response: " . $emailResponse);

        $emailSent = false;
        
        if ($emailResponse === false) {
            error_log("Elastic Email v4 failed: " . $curlError);
        } else {
            $responseData = json_decode($emailResponse, true);
            
            if ($httpCode === 200 && isset($responseData['MessageID'])) {
                $emailSent = true;
                error_log("Elastic Email v4 success - Message ID: " . $responseData['MessageID']);
            } else {
                $errorMsg = $responseData['Error'] ?? $responseData['message'] ?? 'Unknown error';
                error_log("Elastic Email v4 error: " . $errorMsg);
            }
        }
        
        
        if (!$emailSent) {
            error_log("Attempting fallback with PHP mail() to: " . $email);
            
            $subject = 'Your OTP Code - Ethiopian Voting System';
            $message = "
                Ethiopian Voting System
                
                Your One-Time Password (OTP) is: {$otp}
                
                This OTP will expire in 1 minute.
                
                If you didn't request this code, please ignore this email.
                
                Thank you,
                Ethiopian Voting System Team
            ";
            
            $headers = "From: heven3518@gmail.com\r\n";
            $headers .= "Reply-To: heven3518@gmail.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            if (mail($email, $subject, $message, $headers)) {
                $emailSent = true;
                error_log("PHP mail() sent successfully");
            } else {
                error_log("PHP mail() failed");
            }
        }
        
        if ($emailSent) {
            $response = [
                "status" => "success", 
                "message" => "OTP sent successfully! Check your email."
            ];
            error_log("Email sent successfully");
        } else {
            $response = [
                "status" => "error", 
                "message" => "Failed to send OTP email. Please try again."
            ];
            error_log("All email methods failed");
        }

    } else {
        error_log("No voter found with email: " . $email);
        $response = ["status" => "error", "message" => "Email does not match any voter information."];
    }

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $response = ["status" => "error", "message" => "System error: " . $e->getMessage()];
}

error_log("Final response: " . json_encode($response));
error_log("=== OTP EMAIL SENDING COMPLETED ===");

echo json_encode($response);
exit;
?>