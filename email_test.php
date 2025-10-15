<?php
// Email Troubleshooting Tool for JerseyTron
// Access this file directly in your browser: http://yoursite.com/email_test.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1a324f;
            margin-top: 0;
        }
        h2 {
            color: #1a324f;
            border-bottom: 2px solid #1a324f;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border-radius: 6px;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status.pass {
            background-color: #d4edda;
            color: #155724;
        }
        .status.fail {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status.warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .detail {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #007bff;
            margin: 10px 0;
            font-family: monospace;
            font-size: 0.9em;
        }
        .error-detail {
            background-color: #f8d7da;
            padding: 10px;
            border-left: 4px solid #dc3545;
            margin: 10px 0;
            color: #721c24;
        }
        .info {
            background-color: #d1ecf1;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #0c5460;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            margin: 10px 5px 10px 0;
        }
        button:hover {
            background-color: #0056b3;
        }
        button.success {
            background-color: #28a745;
        }
        button.success:hover {
            background-color: #218838;
        }
        .test-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        ul {
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>?? Email Configuration Troubleshooting</h1>
        
        <?php
        // Test 1: Check if config.php exists and loads
        echo "<h2>Test 1: Configuration File</h2>";
        echo "<div class='test-section'>";
        
        if (file_exists('config.php')) {
            echo "? config.php exists <span class='status pass'>PASS</span>";
            require_once 'config.php';
            echo "<div class='detail'>Config file loaded successfully</div>";
        } else {
            echo "? config.php not found <span class='status fail'>FAIL</span>";
            echo "<div class='error-detail'>Please ensure config.php exists in the same directory as this test file.</div>";
            die();
        }
        echo "</div>";
        
        // Test 2: Check PHPMailer installation
        echo "<h2>Test 2: PHPMailer Installation</h2>";
        echo "<div class='test-section'>";
        
        if (file_exists('vendor/autoload.php')) {
            echo "? Composer autoload found <span class='status pass'>PASS</span>";
            require_once 'vendor/autoload.php';
            
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                echo "<div class='detail'>PHPMailer class loaded successfully</div>";
            } else {
                echo "<div class='error-detail'>PHPMailer class not found. Run: composer install</div>";
                die();
            }
        } else {
            echo "? Composer autoload not found <span class='status fail'>FAIL</span>";
            echo "<div class='error-detail'>Run <code>composer install</code> in your project directory</div>";
            die();
        }
        echo "</div>";
        
        // Test 3: Check SMTP Configuration
        echo "<h2>Test 3: SMTP Configuration</h2>";
        echo "<div class='test-section'>";
        
        $configErrors = [];
        
        if (!defined('SMTP_ENABLED')) {
            $configErrors[] = "SMTP_ENABLED not defined";
        } else {
            echo "<div class='detail'>SMTP Enabled: " . (SMTP_ENABLED ? 'Yes' : 'No') . "</div>";
        }
        
        if (SMTP_ENABLED) {
            if (!defined('SMTP_HOST') || SMTP_HOST == '') {
                $configErrors[] = "SMTP_HOST not configured";
            } else {
                echo "<div class='detail'>SMTP Host: " . SMTP_HOST . "</div>";
            }
            
            if (!defined('SMTP_USERNAME') || SMTP_USERNAME == 'your-email@gmail.com') {
                $configErrors[] = "SMTP_USERNAME not configured (still using default placeholder)";
            } else {
                echo "<div class='detail'>SMTP Username: " . SMTP_USERNAME . "</div>";
            }
            
            if (!defined('SMTP_PASSWORD') || SMTP_PASSWORD == 'your-gmail-app-password') {
                $configErrors[] = "SMTP_PASSWORD not configured (still using default placeholder)";
            } else {
                echo "<div class='detail'>SMTP Password: " . str_repeat('*', strlen(SMTP_PASSWORD)) . " (hidden)</div>";
            }
            
            if (!defined('SMTP_PORT') || SMTP_PORT == '') {
                $configErrors[] = "SMTP_PORT not configured";
            } else {
                echo "<div class='detail'>SMTP Port: " . SMTP_PORT . "</div>";
            }
            
            if (!defined('SMTP_SECURE') || SMTP_SECURE == '') {
                $configErrors[] = "SMTP_SECURE not configured";
            } else {
                echo "<div class='detail'>SMTP Encryption: " . SMTP_SECURE . "</div>";
            }
        }
        
        if (!defined('RECIPIENT_EMAIL') || RECIPIENT_EMAIL == '') {
            $configErrors[] = "RECIPIENT_EMAIL not configured";
        } else {
            echo "<div class='detail'>Recipient Email: " . RECIPIENT_EMAIL . "</div>";
        }
        
        if (count($configErrors) > 0) {
            echo "<span class='status fail'>FAIL</span>";
            echo "<div class='error-detail'><strong>Configuration Issues Found:</strong><ul>";
            foreach ($configErrors as $error) {
                echo "<li>" . $error . "</li>";
            }
            echo "</ul></div>";
            
            echo "<div class='info'>";
            echo "<strong>How to fix:</strong><br>";
            echo "1. Open <code>config.php</code><br>";
            echo "2. Update these values with your actual Gmail credentials:<br>";
            echo "<code>define('SMTP_USERNAME', 'your-actual-email@gmail.com');</code><br>";
            echo "<code>define('SMTP_PASSWORD', 'your-gmail-app-password');</code><br><br>";
            echo "<strong>Note:</strong> You need a Gmail App Password (not your regular password). ";
            echo "<a href='https://support.google.com/accounts/answer/185833' target='_blank'>Click here to learn how to create one</a>";
            echo "</div>";
        } else {
            echo "<span class='status pass'>PASS</span>";
        }
        
        echo "</div>";
        
        // Test 4: Check PHP Extensions
        echo "<h2>Test 4: PHP Extensions</h2>";
        echo "<div class='test-section'>";
        
        $requiredExtensions = ['openssl', 'sockets'];
        $missingExtensions = [];
        
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo "? " . $ext . " extension loaded <span class='status pass'>PASS</span><br>";
            } else {
                echo "? " . $ext . " extension missing <span class='status fail'>FAIL</span><br>";
                $missingExtensions[] = $ext;
            }
        }
        
        if (count($missingExtensions) > 0) {
            echo "<div class='error-detail'>Missing extensions: " . implode(', ', $missingExtensions) . "<br>";
            echo "Contact your hosting provider to enable these PHP extensions.</div>";
        }
        
        echo "</div>";
        
        // Test 5: SMTP Connection Test
        if (count($configErrors) == 0 && SMTP_ENABLED) {
            echo "<h2>Test 5: SMTP Connection</h2>";
            echo "<div class='test-section'>";
            
            try {
                $testMail = new PHPMailer\PHPMailer\PHPMailer(true);
                $testMail->isSMTP();
                $testMail->Host = SMTP_HOST;
                $testMail->SMTPAuth = true;
                $testMail->Username = SMTP_USERNAME;
                $testMail->Password = SMTP_PASSWORD;
                $testMail->SMTPSecure = SMTP_SECURE;
                $testMail->Port = SMTP_PORT;
                $testMail->Timeout = 10;
                $testMail->SMTPDebug = 0;
                
                // Try to connect
                if ($testMail->smtpConnect()) {
                    echo "? Successfully connected to SMTP server <span class='status pass'>PASS</span>";
                    echo "<div class='detail'>Connection to " . SMTP_HOST . ":" . SMTP_PORT . " successful</div>";
                    $testMail->smtpClose();
                } else {
                    echo "? Could not connect to SMTP server <span class='status fail'>FAIL</span>";
                    echo "<div class='error-detail'>Check your SMTP credentials and firewall settings</div>";
                }
            } catch (Exception $e) {
                echo "? SMTP Connection Error <span class='status fail'>FAIL</span>";
                echo "<div class='error-detail'><strong>Error:</strong> " . $e->getMessage() . "</div>";
                
                echo "<div class='info'>";
                echo "<strong>Common issues:</strong><ul>";
                echo "<li>Wrong username or password</li>";
                echo "<li>App Password not generated (if using Gmail)</li>";
                echo "<li>2-Factor Authentication enabled but no App Password</li>";
                echo "<li>Port blocked by firewall (try port 465 instead of 587)</li>";
                echo "<li>SMTP server address incorrect</li>";
                echo "</ul></div>";
            }
            
            echo "</div>";
        }
        
        // Test 6: Send Test Email Form
        if (count($configErrors) == 0) {
            echo "<h2>Test 6: Send Test Email</h2>";
            echo "<div class='test-section'>";
            
            if (isset($_POST['send_test'])) {
                $testEmail = $_POST['test_email'];
                
                try {
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    
                    if (SMTP_ENABLED) {
                        $mail->isSMTP();
                        $mail->Host = SMTP_HOST;
                        $mail->SMTPAuth = true;
                        $mail->Username = SMTP_USERNAME;
                        $mail->Password = SMTP_PASSWORD;
                        $mail->SMTPSecure = SMTP_SECURE;
                        $mail->Port = SMTP_PORT;
                    }
                    
                    $mail->setFrom(SMTP_ENABLED ? SMTP_USERNAME : 'noreply@' . $_SERVER['HTTP_HOST'], 'JerseyTron Test');
                    $mail->addAddress($testEmail);
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'JerseyTron Email Test - ' . date('Y-m-d H:i:s');
                    $mail->Body = '<h1>Test Email Successful!</h1><p>Your JerseyTron email configuration is working correctly.</p><p>Sent at: ' . date('Y-m-d H:i:s') . '</p>';
                    $mail->AltBody = 'Test Email Successful! Your JerseyTron email configuration is working correctly. Sent at: ' . date('Y-m-d H:i:s');
                    
                    $mail->send();
                    
                    echo "<span class='status pass'>SUCCESS!</span>";
                    echo "<div class='detail'>Test email sent successfully to: " . htmlspecialchars($testEmail) . "</div>";
                    echo "<div class='info'>Check your inbox (and spam folder) for the test email.</div>";
                    
                } catch (Exception $e) {
                    echo "<span class='status fail'>FAILED</span>";
                    echo "<div class='error-detail'><strong>Error sending email:</strong><br>" . $e->getMessage() . "</div>";
                }
            }
            
            echo "<form method='POST' class='test-form'>";
            echo "<div class='form-group'>";
            echo "<label>Send test email to:</label>";
            echo "<input type='email' name='test_email' value='" . (defined('RECIPIENT_EMAIL') ? RECIPIENT_EMAIL : '') . "' required>";
            echo "</div>";
            echo "<button type='submit' name='send_test' class='success'>Send Test Email</button>";
            echo "</form>";
            
            echo "</div>";
        }
        
        // Summary
        echo "<h2>Summary & Next Steps</h2>";
        echo "<div class='test-section'>";
        
        if (count($configErrors) == 0) {
            echo "<div class='info'>";
            echo "<strong>? Configuration looks good!</strong><br><br>";
            echo "If the test email above worked, your email system is functioning properly.<br><br>";
            echo "<strong>If emails still aren't sending from the main app:</strong><ul>";
            echo "<li>Check <code>handle_send_debug.log</code> for detailed error messages</li>";
            echo "<li>Verify the form is submitting correctly</li>";
            echo "<li>Check your spam/junk folder</li>";
            echo "<li>Try sending to a different email address</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div class='error-detail'>";
            echo "<strong>? Configuration issues found</strong><br><br>";
            echo "Please fix the configuration errors above before testing email functionality.";
            echo "</div>";
        }
        
        echo "</div>";
        
        // Check debug log
        echo "<h2>Debug Log Check</h2>";
        echo "<div class='test-section'>";
        
        if (file_exists('handle_send_debug.log')) {
            echo "? Debug log file exists<br>";
            $logContents = file_get_contents('handle_send_debug.log');
            $logLines = explode("\n", $logContents);
            $lastLines = array_slice($logLines, -20); // Last 20 lines
            
            echo "<div class='detail'>";
            echo "<strong>Last 20 lines of handle_send_debug.log:</strong><br>";
            echo "<pre style='overflow-x: auto; max-height: 300px;'>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
            echo "</div>";
        } else {
            echo "<span class='status warning'>INFO</span>";
            echo "<div class='info'>No debug log found yet. The log file will be created when you first try to send an email from the form.</div>";
        }
        
        echo "</div>";
        ?>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #ccc; text-align: center; color: #666;">
            <p><a href="index.php">? Back to Jersey Designer</a></p>
            <p><small>For security, delete this file after troubleshooting is complete.</small></p>
        </div>
    </div>
</body>
</html>