<?php
session_start();
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Unknown error"];

try {
    $host = 'localhost';
    $db   = 'ethiovote';
    $user = 'root';
    $pass = 'ZAde3518.';
    $pdo  = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (empty($_POST['email'])) {
        echo json_encode(["status" => "error", "message" => "Email not provided."]);
        exit;
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    $pdo->exec("SET @otp = ''");
    $pdo->exec("SET @result = 0");

    $stmt = $pdo->prepare("CALL Check_Email_And_Generate_OTP(:email_input, @otp, @result)");
    $stmt->bindParam(':email_input', $email, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();

    $out = $pdo->query("SELECT @otp AS otp, @result AS result")->fetch(PDO::FETCH_ASSOC);

    if ($out && $out['result'] == 1) {
        $otp = $out['otp']; 
        $apiKey = "89BEEDCDB918A388BDDE702B1ABA8493F1199D291EEB4B30AE54AC9E37930661440C4F61025C1FF11F5B2D1A841960E2";
        $post = [
            'apikey'     => $apiKey,
            'from'       => 'heven3518@gmail.com',
            'fromName'   => 'Ethiopian Voting System',
            'to'         => $email,
            'subject'    => 'Your OTP Verification Code - Ethiopian Voting System',
            'bodyHtml'   => "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body { 
                            font-family: 'Arial', sans-serif; 
                            line-height: 1.6; 
                            color: #333; 
                            max-width: 600px; 
                            margin: 0 auto; 
                            padding: 20px;
                        }
                        .header { 
                            background: linear-gradient(135deg, #2E8B57, #228B22);
                            color: white; 
                            padding: 30px; 
                            text-align: center; 
                            border-radius: 10px 10px 0 0;
                        }
                        .content { 
                            background: #f9f9f9; 
                            padding: 30px; 
                            border-radius: 0 0 10px 10px;
                            border: 1px solid #e0e0e0;
                        }
                        .otp-box { 
                            background: white; 
                            padding: 25px; 
                            margin: 20px 0; 
                            text-align: center; 
                            border-radius: 8px; 
                            border: 2px dashed #2E8B57;
                            font-size: 32px; 
                            font-weight: bold; 
                            color: #2E8B57; 
                            letter-spacing: 8px;
                        }
                        .footer { 
                            text-align: center; 
                            margin-top: 30px; 
                            padding-top: 20px; 
                            border-top: 1px solid #e0e0e0; 
                            color: #666; 
                            font-size: 12px;
                        }
                        .warning { 
                            background: #fff3cd; 
                            border: 1px solid #ffeaa7; 
                            padding: 15px; 
                            border-radius: 5px; 
                            margin: 20px 0;
                            color: #856404;
                        }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h1>🇪🇹 Ethiopian Voting System</h1>
                        <p>Secure Digital Voting Platform</p>
                    </div>
                    
                    <div class='content'>
                        <h2>OTP Verification Code</h2>
                        <p>Dear Voter,</p>
                        <p>Your One-Time Password (OTP) for account verification is:</p>
                        
                        <div class='otp-box'>
                            {$otp}
                        </div>
                        
                        <p>Please use this code to complete your verification process.</p>
                        
                        <div class='warning'>
                            <strong>⚠️ Important Security Notice:</strong>
                            <ul>
                                <li>This OTP is valid for <strong>1 minute only</strong></li>
                                <li>Do not share this code with anyone</li>
                                <li>Our team will never ask for your OTP</li>
                                <li>If you didn't request this code, please ignore this email</li>
                            </ul>
                        </div>
                        
                        <p>Thank you for using the Ethiopian Voting System.</p>
                        <p><strong>Ensuring Free and Fair Elections</strong></p>
                    </div>
                    
                    <div class='footer'>
                        <p>© 2024 Ethiopian Voting System. All rights reserved.</p>
                        <p>This is an automated message, please do not reply to this email.</p>
                    </div>
                </body>
                </html>
            ",
            'bodyText'   => "
ETHIOPIAN VOTING SYSTEM
=======================

OTP Verification Code: {$otp}

Dear Voter,

Your One-Time Password (OTP) for account verification is: {$otp}

Please use this code to complete your verification process.

Important Security Notice:
- This OTP is valid for 1 minute only
- Do not share this code with anyone
- Our team will never ask for your OTP
- If you didn't request this code, please ignore this email

Thank you for using the Ethiopian Voting System.

Ensuring Free and Fair Elections

© 2024 Ethiopian Voting System. All rights reserved.
This is an automated message, please do not reply to this email.
            ",
            'isTransactional' => true
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.elasticemail.com/v2/email/send",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_RETURNTRANSFER => true
        ]);

        $emailResponse = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($emailResponse) {
            $_SESSION['Rest_user_email'] = $email;
            $response['status'] = 'success';
            $response['message'] = 'OTP sent successfully';
        } else {
            throw new Exception("Failed to send OTP: $curlError");
        }

    } else {
        $response = ["status" => "error", "message" => "Email does not match any voter information."];
    }

} catch (PDOException $e) {
    $response = ["status" => "error", "message" => "Database error: " . $e->getMessage()];
} catch (Exception $e) {
    $response = ["status" => "error", "message" => "An error occurred: " . $e->getMessage()];
}

echo json_encode($response);
exit;
?>